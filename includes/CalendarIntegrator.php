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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set a timeout for the request

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300 && $data !== false) {
            // Guardar en cache (no crítico)
            try {
                @file_put_contents($cacheFile, $data);
            } catch (Exception $e) {
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
                if (str_starts_with($line, 'DTSTART;VALUE=DATE:')) {
                    $currentEvent['DTSTART'] = substr($line, strlen('DTSTART;VALUE=DATE:'));
                } elseif (str_starts_with($line, 'DTEND;VALUE=DATE:')) {
                    $currentEvent['DTEND'] = substr($line, strlen('DTEND;VALUE=DATE:'));
                } elseif (str_starts_with($line, 'SUMMARY:')) {
                    $currentEvent['SUMMARY'] = substr($line, strlen('SUMMARY:'));
                } elseif (str_starts_with($line, 'UID:')) {
                    $currentEvent['UID'] = substr($line, strlen('UID:'));
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
            if (isset($event['DTSTART']) && isset($event['DTEND'])) {
                try {
                    $startDate = new DateTime($event['DTSTART']);
                    // DTEND in iCal is exclusive, so subtract one day to get the last occupied day
                    $endDate = new DateTime($event['DTEND']);
                    $endDate->modify('-1 day');

                    $currentDate = clone $startDate;
                    while ($currentDate <= $endDate) {
                        $occupiedDates[] = $currentDate->format('Y-m-d');
                        $currentDate->modify('+1 day');
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
