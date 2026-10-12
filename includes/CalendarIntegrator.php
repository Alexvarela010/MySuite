<?php

class CalendarIntegrator {

    /**
     * TTL por defecto para cache de iCal (segundos)
     * Cambiar según necesidades (ej. 300 = 5 minutos)
     */
    public int $cacheTtl = 300;

    /**
     * Ruta de cache para iCal
     */
    protected string $cacheDir;

    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../cache/ical';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Fetches iCal data from a given URL.
     *
     * @param string $url The URL of the iCal file.
     * @return string|null The iCal content as a string, or null on failure.
     */
    public function fetchIcalUrl(string $url): ?string {
        // Intentar usar cache
        try {
            $cacheFile = $this->cacheDir . '/' . md5($url) . '.ics';
            if (is_file($cacheFile)) {
                $age = time() - filemtime($cacheFile);
                if ($age <= $this->cacheTtl) {
                    $content = @file_get_contents($cacheFile);
                    if ($content !== false) {
                        return $content;
                    }
                }
            }
        } catch (Exception $e) {
            // No crítico, seguimos a obtener por red
            error_log('Error al leer cache iCal: ' . $e->getMessage());
        }

        $data = false;
        $httpCode = 0;
        $error = '';

        try {
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MySuiteBot/1.0)');

                $data = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (PHP_VERSION_ID < 80500) {
                    @curl_close($ch);
                }
            } else {
                // Fallback usando file_get_contents con stream_context
                $ctx = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 10,
                        'follow_location' => 1,
                        'header' => "User-Agent: Mozilla/5.0 (compatible; MySuiteBot/1.0)\r\n"
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]);
                $data = @file_get_contents($url, false, $ctx);
                $httpCode = ($data !== false) ? 200 : 500;
            }
        } catch (\Throwable $t) {
            error_log("Exception while fetching iCal from $url: " . $t->getMessage());
            $data = false;
        }

        if ($httpCode >= 200 && $httpCode < 300 && $data !== false) {
            // Guardar en cache (no crítico)
            try {
                if (isset($cacheFile)) {
                    @file_put_contents($cacheFile, $data);
                }
            } catch (\Throwable $e) {
                error_log('Error al escribir cache iCal: ' . $e->getMessage());
            }
            return $data;
        } else {
            error_log("Failed to fetch iCal from $url. HTTP Code: $httpCode. Error: $error");
            // Si hay cache vieja, devolverla aunque esté caducada (mejor que nada)
            if (isset($cacheFile) && is_file($cacheFile)) {
                $content = @file_get_contents($cacheFile);
                if ($content !== false) {
                    return $content;
                }
            }
            return null;
        }
    }

    /**
     * Parses iCal content and extracts event details.
     *
     * @param string $icalContent The raw iCal content string.
     * @return array An array of event arrays, each containing DTSTART, DTEND, and SUMMARY.
     */
    public function parseIcalData(string $icalContent): array {
        $events = [];
        $lines = explode("\n", $icalContent);
        $currentEvent = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === 'BEGIN:VEVENT') {
                $currentEvent = [];
            } elseif ($line === 'END:VEVENT') {
                if ($currentEvent) {
                    $events[] = $currentEvent;
                    $currentEvent = null;
                }
            } elseif ($currentEvent !== null) {
                if (preg_match('/^DTSTART(?:;[^:]*)?:(.*)$/i', $line, $matches)) {
                    $currentEvent['DTSTART'] = trim($matches[1]);
                } elseif (preg_match('/^DTEND(?:;[^:]*)?:(.*)$/i', $line, $matches)) {
                    $currentEvent['DTEND'] = trim($matches[1]);
                } elseif (preg_match('/^SUMMARY:(.*)$/i', $line, $matches)) {
                    $currentEvent['SUMMARY'] = trim($matches[1]);
                } elseif (preg_match('/^UID:(.*)$/i', $line, $matches)) {
                    $currentEvent['UID'] = trim($matches[1]);
                } elseif (preg_match('/^DESCRIPTION:(.*)$/i', $line, $matches)) {
                    $currentEvent['DESCRIPTION'] = trim($matches[1]);
                }
            }
        }
        return $events;
    }

    /**
     * Converts a list of iCal events into a unique list of occupied dates.
     *
     * @param array $icalEvents An array of event arrays (from parseIcalData).
     * @return array A sorted array of unique occupied dates in 'YYYY-MM-DD' format.
     */
    public function getOccupiedDates(array $icalEvents): array {
        $occupiedDates = [];
        foreach ($icalEvents as $event) {
            if (isset($event['DTSTART']) && !empty($event['DTSTART'])) {
                try {
                    $startDate = new DateTime($event['DTSTART']);
                    $startDay = $startDate->format('Y-m-d');

                    if (isset($event['DTEND']) && !empty($event['DTEND'])) {
                        $endDate = new DateTime($event['DTEND']);
                        $endDay = $endDate->format('Y-m-d');

                        // Si DTSTART y DTEND son el mismo día (ej. bloqueo de un solo día en Forever Summer)
                        if ($startDay === $endDay) {
                            $occupiedDates[] = $startDay;
                        } elseif ($startDate < $endDate) {
                            // DTEND en iCal estándar para estancias multi-día es exclusivo
                            $endDate->modify('-1 day');
                            $currentDate = clone $startDate;
                            while ($currentDate <= $endDate) {
                                $occupiedDates[] = $currentDate->format('Y-m-d');
                                $currentDate->modify('+1 day');
                            }
                        } else {
                            $occupiedDates[] = $startDay;
                        }
                    } else {
                        // Evento de un solo día sin DTEND explícito
                        $occupiedDates[] = $startDay;
                    }
                } catch (Exception $e) {
                    error_log("Error parsing date for event: " . $e->getMessage() . " Event: " . json_encode($event));
                }
            }
        }
        $occupiedDates = array_unique($occupiedDates);
        sort($occupiedDates);
        return $occupiedDates;
    }
}
