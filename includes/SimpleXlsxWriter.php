<?php
/**
 * SimpleXlsxWriter - Generador ligero de archivos Excel nativos (.xlsx)
 * My Suite In Cartagena
 * 100% compatible con Microsoft Excel, LibreOffice y Google Sheets sin alertas de formato.
 */

class SimpleXlsxWriter {

    /**
     * Convierte índice 0-based de columna en letra Excel (0 -> A, 27 -> AB)
     */
    public static function getColLetter(int $colIndex): string {
        $letter = '';
        $colIndex++;
        while ($colIndex > 0) {
            $rem = ($colIndex - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $colIndex = (int)(($colIndex - $rem) / 26);
        }
        return $letter;
    }

    /**
     * Escapa caracteres especiales XML
     */
    public static function xmlEscape($str): string {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Genera el archivo .xlsx en disco en la ruta especificada
     */
    public static function generateXlsxFile(array $movimientos, array $totales, array $filters, string $outputPath): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $periodo = (!empty($filters['mes']) && isset($meses[$filters['mes']]) ? $meses[$filters['mes']] . ' ' : '') . (!empty($filters['anio']) ? $filters['anio'] : 'Histórico Completo');
        $tipoFiltro = !empty($filters['tipo']) ? strtoupper($filters['tipo']) : 'TODOS';

        $totales['total_entradas'] = (float)($totales['total_entradas'] ?? 0);
        $totales['total_salidas'] = (float)($totales['total_salidas'] ?? 0);
        $totales['balance_neto'] = (float)($totales['balance_neto'] ?? ($totales['total_entradas'] - $totales['total_salidas']));
        $totales['total_registros'] = (int)($totales['total_registros'] ?? count($movimientos));

        // 1. Construir XML de la hoja (sheet1.xml)
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $sheetXml .= '  <sheetViews>' . "\n";
        $sheetXml .= '    <sheetView tabSelected="1" workbookViewId="0" showGridLines="1"/>' . "\n";
        $sheetXml .= '  </sheetViews>' . "\n";
        
        // Configurar anchos de columna (16 columnas de A a P)
        $sheetXml .= '  <cols>' . "\n";
        $colWidths = [
            1 => 10,  // A: ID
            2 => 14,  // B: Fecha
            3 => 8,   // C: Día
            4 => 13,  // D: Mes
            5 => 9,   // E: Año
            6 => 12,  // F: Tipo
            7 => 15,  // G: Caja
            8 => 28,  // H: Categoría
            9 => 20,  // I: Monto (COP)
            10 => 24, // J: Método de Pago
            11 => 22, // K: Responsable
            12 => 38, // L: Descripción / Concepto
            13 => 18, // M: # Comprobante
            14 => 12, // N: # Reserva
            15 => 22, // O: Registrado Por
            16 => 18  // P: Fecha Registro
        ];
        foreach ($colWidths as $col => $w) {
            $sheetXml .= "    <col min=\"{$col}\" max=\"{$col}\" width=\"{$w}\" customWidth=\"1\"/>\n";
        }
        $sheetXml .= '  </cols>' . "\n";
        $sheetXml .= '  <sheetData>' . "\n";

        $rowIdx = 1;
        $merges = [];

        // Fila 1: Título Principal (Centrado y combinado de A a P)
        $sheetXml .= "    <row r=\"{$rowIdx}\" ht=\"32\" customHeight=\"1\">\n";
        $sheetXml .= "      <c r=\"A{$rowIdx}\" s=\"1\" t=\"inlineStr\"><is><t>" . self::xmlEscape("MY SUITE IN CARTAGENA - LIBRO DE CONTABILIDAD") . "</t></is></c>\n";
        for ($c = 1; $c < 16; $c++) {
            $sheetXml .= "      <c r=\"" . self::getColLetter($c) . "{$rowIdx}\" s=\"1\"/>\n";
        }
        $sheetXml .= "    </row>\n";
        $merges[] = "A{$rowIdx}:P{$rowIdx}";
        $rowIdx++;

        // Fila 2: Subtítulo con filtros y fecha (Centrado y combinado de A a P)
        $sheetXml .= "    <row r=\"{$rowIdx}\" ht=\"20\" customHeight=\"1\">\n";
        $cajaFiltroStr = !empty($filters['caja']) ? ' | Caja: ' . strtoupper($filters['caja']) : '';
        $subtitulo = "Período: {$periodo} | Tipo: {$tipoFiltro}{$cajaFiltroStr} | Generado el: " . date('Y-m-d H:i:s');
        $sheetXml .= "      <c r=\"A{$rowIdx}\" s=\"2\" t=\"inlineStr\"><is><t>" . self::xmlEscape($subtitulo) . "</t></is></c>\n";
        for ($c = 1; $c < 16; $c++) {
            $sheetXml .= "      <c r=\"" . self::getColLetter($c) . "{$rowIdx}\" s=\"2\"/>\n";
        }
        $sheetXml .= "    </row>\n";
        $merges[] = "A{$rowIdx}:P{$rowIdx}";
        $rowIdx++;

        // Fila 3: Espacio
        $rowIdx++;

        // Fila 4: Tarjetas de Resumen (5 tarjetas de 3 columnas cada una, de B a P)
        $cardLabelRow = $rowIdx;
        $sheetXml .= "    <row r=\"{$cardLabelRow}\" ht=\"22\" customHeight=\"1\">\n";
        // B..D: TOTAL ENTRADAS
        $sheetXml .= "      <c r=\"B{$cardLabelRow}\" s=\"8\" t=\"inlineStr\"><is><t>TOTAL ENTRADAS</t></is></c>\n";
        $sheetXml .= "      <c r=\"C{$cardLabelRow}\" s=\"8\"/>\n";
        $sheetXml .= "      <c r=\"D{$cardLabelRow}\" s=\"8\"/>\n";
        // E..G: TOTAL SALIDAS
        $sheetXml .= "      <c r=\"E{$cardLabelRow}\" s=\"8\" t=\"inlineStr\"><is><t>TOTAL SALIDAS</t></is></c>\n";
        $sheetXml .= "      <c r=\"F{$cardLabelRow}\" s=\"8\"/>\n";
        $sheetXml .= "      <c r=\"G{$cardLabelRow}\" s=\"8\"/>\n";
        // H..J: BALANCE NETO
        $sheetXml .= "      <c r=\"H{$cardLabelRow}\" s=\"8\" t=\"inlineStr\"><is><t>BALANCE NETO</t></is></c>\n";
        $sheetXml .= "      <c r=\"I{$cardLabelRow}\" s=\"8\"/>\n";
        $sheetXml .= "      <c r=\"J{$cardLabelRow}\" s=\"8\"/>\n";
        // K..M: SALDO CAJA EVELYN
        $sheetXml .= "      <c r=\"K{$cardLabelRow}\" s=\"8\" t=\"inlineStr\"><is><t>SALDO CAJA EVELYN</t></is></c>\n";
        $sheetXml .= "      <c r=\"L{$cardLabelRow}\" s=\"8\"/>\n";
        $sheetXml .= "      <c r=\"M{$cardLabelRow}\" s=\"8\"/>\n";
        // N..P: SALDO CAJA CARLOS
        $sheetXml .= "      <c r=\"N{$cardLabelRow}\" s=\"8\" t=\"inlineStr\"><is><t>SALDO CAJA CARLOS</t></is></c>\n";
        $sheetXml .= "      <c r=\"O{$cardLabelRow}\" s=\"8\"/>\n";
        $sheetXml .= "      <c r=\"P{$cardLabelRow}\" s=\"8\"/>\n";
        $sheetXml .= "    </row>\n";
        $merges[] = "B{$cardLabelRow}:D{$cardLabelRow}";
        $merges[] = "E{$cardLabelRow}:G{$cardLabelRow}";
        $merges[] = "H{$cardLabelRow}:J{$cardLabelRow}";
        $merges[] = "K{$cardLabelRow}:M{$cardLabelRow}";
        $merges[] = "N{$cardLabelRow}:P{$cardLabelRow}";
        $rowIdx++;

        // Fila 5: Tarjetas de Resumen (Valores - Combinadas de 3 en 3 columnas)
        $cardValueRow = $rowIdx;
        $sheetXml .= "    <row r=\"{$cardValueRow}\" ht=\"28\" customHeight=\"1\">\n";
        // B..D: Valor Entradas
        $sheetXml .= "      <c r=\"B{$cardValueRow}\" s=\"9\"><v>" . (float)$totales['total_entradas'] . "</v></c>\n";
        $sheetXml .= "      <c r=\"C{$cardValueRow}\" s=\"9\"/>\n";
        $sheetXml .= "      <c r=\"D{$cardValueRow}\" s=\"9\"/>\n";
        // E..G: Valor Salidas
        $sheetXml .= "      <c r=\"E{$cardValueRow}\" s=\"10\"><v>" . (float)$totales['total_salidas'] . "</v></c>\n";
        $sheetXml .= "      <c r=\"F{$cardValueRow}\" s=\"10\"/>\n";
        $sheetXml .= "      <c r=\"G{$cardValueRow}\" s=\"10\"/>\n";
        // H..J: Valor Balance
        $sheetXml .= "      <c r=\"H{$cardValueRow}\" s=\"11\"><v>" . (float)$totales['balance_neto'] . "</v></c>\n";
        $sheetXml .= "      <c r=\"I{$cardValueRow}\" s=\"11\"/>\n";
        $sheetXml .= "      <c r=\"J{$cardValueRow}\" s=\"11\"/>\n";
        // K..M: Valor Saldo Caja Evelyn
        $sheetXml .= "      <c r=\"K{$cardValueRow}\" s=\"9\"><v>" . (float)($totales['saldo_caja_evelyn'] ?? 0) . "</v></c>\n";
        $sheetXml .= "      <c r=\"L{$cardValueRow}\" s=\"9\"/>\n";
        $sheetXml .= "      <c r=\"M{$cardValueRow}\" s=\"9\"/>\n";
        // N..P: Valor Saldo Caja Carlos
        $sheetXml .= "      <c r=\"N{$cardValueRow}\" s=\"11\"><v>" . (float)($totales['saldo_caja_carlos'] ?? 0) . "</v></c>\n";
        $sheetXml .= "      <c r=\"O{$cardValueRow}\" s=\"11\"/>\n";
        $sheetXml .= "      <c r=\"P{$cardValueRow}\" s=\"11\"/>\n";
        $sheetXml .= "    </row>\n";
        $merges[] = "B{$cardValueRow}:D{$cardValueRow}";
        $merges[] = "E{$cardValueRow}:G{$cardValueRow}";
        $merges[] = "H{$cardValueRow}:J{$cardValueRow}";
        $merges[] = "K{$cardValueRow}:M{$cardValueRow}";
        $merges[] = "N{$cardValueRow}:P{$cardValueRow}";
        $rowIdx++;

        // Fila 6: Espacio
        $rowIdx++;

        // Fila 7: Encabezados de la Tabla
        $sheetXml .= "    <row r=\"{$rowIdx}\" ht=\"26\" customHeight=\"1\">\n";
        $headers = [
            '# ID', 'Fecha', 'Día', 'Mes', 'Año', 'Tipo', 'Caja', 'Categoría / Concepto',
            'Monto (COP)', 'Método de Pago', 'Responsable', 'Descripción / Detalle',
            '# Comprobante', '# Reserva', 'Registrado Por', 'Fecha Registro'
        ];
        foreach ($headers as $cIdx => $h) {
            $colLetter = self::getColLetter($cIdx);
            $sheetXml .= "      <c r=\"{$colLetter}{$rowIdx}\" s=\"3\" t=\"inlineStr\"><is><t>" . self::xmlEscape($h) . "</t></is></c>\n";
        }
        $sheetXml .= "    </row>\n";
        $rowIdx++;

        // Filas de datos
        if (empty($movimientos)) {
            $sheetXml .= "    <row r=\"{$rowIdx}\" ht=\"24\" customHeight=\"1\">\n";
            $sheetXml .= "      <c r=\"A{$rowIdx}\" s=\"4\" t=\"inlineStr\"><is><t>No hay movimientos registrados para el período o filtros seleccionados.</t></is></c>\n";
            for ($c = 1; $c < 16; $c++) {
                $sheetXml .= "      <c r=\"" . self::getColLetter($c) . "{$rowIdx}\" s=\"4\"/>\n";
            }
            $sheetXml .= "    </row>\n";
            $merges[] = "A{$rowIdx}:P{$rowIdx}";
            $rowIdx++;
        } else {
            foreach ($movimientos as $m) {
                $tipo = strtolower($m['tipo'] ?? 'entrada');
                $isEntrada = ($tipo === 'entrada');
                $fecha = $m['fecha'] ?? date('Y-m-d');
                $time = strtotime($fecha) ?: time();
                $dia = $m['dia'] ?? (int)date('j', $time);
                $mesNum = (int)($m['mes'] ?? date('n', $time));
                $nombreMes = $meses[$mesNum] ?? ($m['mes'] ?? date('F', $time));
                $anio = $m['anio'] ?? (int)date('Y', $time);
                $idMov = $m['id_movimiento'] ?? ($m['id'] ?? '-');
                $caja = (string)($m['caja'] ?? 'Evelyn');
                $categoria = (string)($m['categoria'] ?? '');
                $monto = (float)($m['monto'] ?? 0);
                $metodoPago = (string)($m['metodo_pago'] ?? 'EFECTIVO');
                $responsable = (string)($m['responsable'] ?? 'No especificado');
                $descripcion = (string)($m['descripcion'] ?? '');
                $comprobante = (string)($m['comprobante_numero'] ?? '-');
                $idReserva = (string)($m['id_reserva'] ?? '-');
                $creadoPor = (string)($m['creado_por'] ?? 'Sistema');
                $creadoEn = (string)($m['creado_en'] ?? '-');

                $styleMonto = $isEntrada ? 6 : 7;
                $styleTipo = $isEntrada ? 13 : 14;

                $sheetXml .= "    <row r=\"{$rowIdx}\" ht=\"20\">\n";
                // A: ID
                $sheetXml .= "      <c r=\"A{$rowIdx}\" s=\"5\" t=\"inlineStr\"><is><t>" . self::xmlEscape($idMov) . "</t></is></c>\n";
                // B: Fecha
                $sheetXml .= "      <c r=\"B{$rowIdx}\" s=\"5\" t=\"inlineStr\"><is><t>" . self::xmlEscape($fecha) . "</t></is></c>\n";
                // C: Día
                $sheetXml .= "      <c r=\"C{$rowIdx}\" s=\"5\"><v>{$dia}</v></c>\n";
                // D: Mes
                $sheetXml .= "      <c r=\"D{$rowIdx}\" s=\"5\" t=\"inlineStr\"><is><t>" . self::xmlEscape($nombreMes) . "</t></is></c>\n";
                // E: Año
                $sheetXml .= "      <c r=\"E{$rowIdx}\" s=\"5\"><v>{$anio}</v></c>\n";
                // F: Tipo
                $sheetXml .= "      <c r=\"F{$rowIdx}\" s=\"{$styleTipo}\" t=\"inlineStr\"><is><t>" . strtoupper($tipo) . "</t></is></c>\n";
                // G: Caja
                $sheetXml .= "      <c r=\"G{$rowIdx}\" s=\"5\" t=\"inlineStr\"><is><t>" . self::xmlEscape($caja) . "</t></is></c>\n";
                // H: Categoría
                $sheetXml .= "      <c r=\"H{$rowIdx}\" s=\"4\" t=\"inlineStr\"><is><t>" . self::xmlEscape($categoria) . "</t></is></c>\n";
                // I: Monto
                $sheetXml .= "      <c r=\"I{$rowIdx}\" s=\"{$styleMonto}\"><v>{$monto}</v></c>\n";
                // J: Método Pago
                $sheetXml .= "      <c r=\"J{$rowIdx}\" s=\"4\" t=\"inlineStr\"><is><t>" . self::xmlEscape($metodoPago) . "</t></is></c>\n";
                // K: Responsable
                $sheetXml .= "      <c r=\"K{$rowIdx}\" s=\"4\" t=\"inlineStr\"><is><t>" . self::xmlEscape($responsable) . "</t></is></c>\n";
                // L: Descripción
                $sheetXml .= "      <c r=\"L{$rowIdx}\" s=\"4\" t=\"inlineStr\"><is><t>" . self::xmlEscape($descripcion) . "</t></is></c>\n";
                // M: Comprobante
                $sheetXml .= "      <c r=\"M{$rowIdx}\" s=\"5\" t=\"inlineStr\"><is><t>" . self::xmlEscape($comprobante) . "</t></is></c>\n";
                // N: Reserva
                $sheetXml .= "      <c r=\"N{$rowIdx}\" s=\"5\" t=\"inlineStr\"><is><t>" . self::xmlEscape($idReserva) . "</t></is></c>\n";
                // O: Creado Por
                $sheetXml .= "      <c r=\"O{$rowIdx}\" s=\"4\" t=\"inlineStr\"><is><t>" . self::xmlEscape($creadoPor) . "</t></is></c>\n";
                // P: Fecha Registro
                $sheetXml .= "      <c r=\"P{$rowIdx}\" s=\"5\" t=\"inlineStr\"><is><t>" . self::xmlEscape($creadoEn) . "</t></is></c>\n";
                $sheetXml .= "    </row>\n";
                $rowIdx++;
            }

            // Fila de Totales final (Combinada A:H e J:P)
            $totalesRow = $rowIdx;
            $sheetXml .= "    <row r=\"{$totalesRow}\" ht=\"24\" customHeight=\"1\">\n";
            $sheetXml .= "      <c r=\"A{$totalesRow}\" s=\"15\" t=\"inlineStr\"><is><t>TOTALES:</t></is></c>\n";
            for ($c = 1; $c <= 7; $c++) { // B through H
                $sheetXml .= "      <c r=\"" . self::getColLetter($c) . "{$totalesRow}\" s=\"15\"/>\n";
            }
            $styleTotalesBalance = ($totales['balance_neto'] >= 0) ? 16 : 17;
            $sheetXml .= "      <c r=\"I{$totalesRow}\" s=\"{$styleTotalesBalance}\"><v>" . (float)$totales['balance_neto'] . "</v></c>\n";
            for ($c = 9; $c < 16; $c++) { // J through P
                $sheetXml .= "      <c r=\"" . self::getColLetter($c) . "{$totalesRow}\" s=\"18\"/>\n";
            }
            $sheetXml .= "    </row>\n";
            $merges[] = "A{$totalesRow}:H{$totalesRow}";
            $merges[] = "J{$totalesRow}:P{$totalesRow}";
            $rowIdx++;
        }

        $sheetXml .= '  </sheetData>' . "\n";

        // Celdas combinadas (mergeCells)
        $mergeCount = count($merges);
        $sheetXml .= "  <mergeCells count=\"{$mergeCount}\">\n";
        foreach ($merges as $mRef) {
            $sheetXml .= "    <mergeCell ref=\"{$mRef}\"/>\n";
        }
        $sheetXml .= "  </mergeCells>\n";

        $sheetXml .= '</worksheet>' . "\n";

        // 2. Construir styles.xml
        $stylesXml = self::getStylesXml();

        // 3. Crear ZIP en $outputPath
        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', self::getContentTypesXml());
        // _rels/.rels
        $zip->addFromString('_rels/.rels', self::getRelsXml());
        // xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::getWorkbookRelsXml());
        // xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', self::getWorkbookXml());
        // xl/styles.xml
        $zip->addFromString('xl/styles.xml', $stylesXml);
        // xl/worksheets/sheet1.xml
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
        return file_exists($outputPath) && filesize($outputPath) > 0;
    }

    /**
     * Genera y transmite el archivo .xlsx al navegador para descarga directa
     */
    public static function outputContabilidad(array $movimientos, array $totales, array $filters, string $filename = 'Contabilidad_MySuite.xlsx'): void {
        if (!class_exists('ZipArchive')) {
            self::outputHtmlSpreadsheetFallback($movimientos, $totales, $filters, str_replace('.xlsx', '.xls', $filename));
            return;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $success = self::generateXlsxFile($movimientos, $totales, $filters, $tmpFile);
        if (!$success) {
            self::outputHtmlSpreadsheetFallback($movimientos, $totales, $filters, str_replace('.xlsx', '.xls', $filename));
            return;
        }

        // Limpiar cualquier buffer previo para evitar corromper los bytes binarios del ZIP
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        readfile($tmpFile);
        @unlink($tmpFile);
        exit;
    }

    /**
     * XML de estilos para fuentes, colores, monedas y bordes
     */
    private static function getStylesXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n" .
'  <numFmts count="1">' . "\n" .
'    <numFmt numFmtId="164" formatCode="&quot;$&quot; #,##0;[Red]&quot;$&quot; \-#,##0;&quot;$&quot; 0"/>' . "\n" .
'  </numFmts>' . "\n" .
'  <fonts count="12">' . "\n" .
'    <font><name val="Calibri"/><sz val="11"/><color theme="1"/></font>' . "\n" . // 0: Normal
'    <font><name val="Calibri"/><sz val="16"/><b/><color rgb="FF1E3A8A"/></font>' . "\n" . // 1: Título Azul Marino
'    <font><name val="Calibri"/><sz val="10"/><i/><color rgb="FF475569"/></font>' . "\n" . // 2: Subtítulo Gris
'    <font><name val="Calibri"/><sz val="11"/><b/><color rgb="FFFFFFFF"/></font>' . "\n" . // 3: Header Blanco
'    <font><name val="Calibri"/><sz val="11"/><b/><color rgb="FF15803D"/></font>' . "\n" . // 4: Verde Entrada 11pt
'    <font><name val="Calibri"/><sz val="11"/><b/><color rgb="FFB91C1C"/></font>' . "\n" . // 5: Rojo Salida 11pt
'    <font><name val="Calibri"/><sz val="14"/><b/><color rgb="FF1D4ED8"/></font>' . "\n" . // 6: Azul Balance 14pt
'    <font><name val="Calibri"/><sz val="11"/><b/><color rgb="FF334155"/></font>' . "\n" . // 7: Card Label Bold 11pt
'    <font><name val="Calibri"/><sz val="13"/><b/><color rgb="FF15803D"/></font>' . "\n" . // 8: Card Entrada 13pt
'    <font><name val="Calibri"/><sz val="13"/><b/><color rgb="FFB91C1C"/></font>' . "\n" . // 9: Card Salida 13pt
'    <font><name val="Calibri"/><sz val="13"/><b/><color rgb="FF0F172A"/></font>' . "\n" . // 10: Card Registros 13pt
'    <font><name val="Calibri"/><sz val="11"/><b/><color rgb="FF0F172A"/></font>' . "\n" . // 11: Totales label bold 11pt
'  </fonts>' . "\n" .
'  <fills count="7">' . "\n" .
'    <fill><patternFill patternType="none"/></fill>' . "\n" . // 0
'    <fill><patternFill patternType="gray125"/></fill>' . "\n" . // 1
'    <fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>' . "\n" . // 2: Header Dark Slate
'    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' . "\n" . // 3: Card Background
'    <fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/></patternFill></fill>' . "\n" . // 4: Verde Suave Badge
'    <fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/></patternFill></fill>' . "\n" . // 5: Rojo Suave Badge
'    <fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/></patternFill></fill>' . "\n" . // 6: Fila Totales
'  </fills>' . "\n" .
'  <borders count="3">' . "\n" .
'    <border><left/><right/><top/><bottom/><diagonal/></border>' . "\n" . // 0: Ninguno
'    <border>' . "\n" . // 1: Delgado tabla #cbd5e1
'      <left style="thin"><color rgb="FFCBD5E1"/></left>' . "\n" .
'      <right style="thin"><color rgb="FFCBD5E1"/></right>' . "\n" .
'      <top style="thin"><color rgb="FFCBD5E1"/></top>' . "\n" .
'      <bottom style="thin"><color rgb="FFCBD5E1"/></bottom>' . "\n" .
'    </border>' . "\n" .
'    <border>' . "\n" . // 2: Medio / Card border #94a3b8
'      <left style="thin"><color rgb="FF94A3B8"/></left>' . "\n" .
'      <right style="thin"><color rgb="FF94A3B8"/></right>' . "\n" .
'      <top style="thin"><color rgb="FF94A3B8"/></top>' . "\n" .
'      <bottom style="thin"><color rgb="FF94A3B8"/></bottom>' . "\n" .
'    </border>' . "\n" .
'  </borders>' . "\n" .
'  <cellXfs count="19">' . "\n" .
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' . "\n" . // 0: Default
'    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 1: Título Centrado
'    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 2: Subtítulo Centrado
'    <xf numFmtId="0" fontId="3" fillId="2" borderId="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 3: Th
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyAlignment="1"><alignment vertical="center"/></xf>' . "\n" . // 4: Td normal
'    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 5: Td centro
'    <xf numFmtId="164" fontId="4" fillId="0" borderId="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 6: Monto Entrada
'    <xf numFmtId="164" fontId="5" fillId="0" borderId="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 7: Monto Salida
'    <xf numFmtId="0" fontId="7" fillId="3" borderId="2" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 8: Card Label Centrado
'    <xf numFmtId="164" fontId="8" fillId="3" borderId="2" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 9: Card Entrada Value Centrado
'    <xf numFmtId="164" fontId="9" fillId="3" borderId="2" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 10: Card Salida Value Centrado
'    <xf numFmtId="164" fontId="6" fillId="3" borderId="2" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 11: Card Balance Value Centrado
'    <xf numFmtId="0" fontId="10" fillId="3" borderId="2" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 12: Card Registros Value Centrado
'    <xf numFmtId="0" fontId="4" fillId="4" borderId="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 13: Badge Entrada Centrado
'    <xf numFmtId="0" fontId="5" fillId="5" borderId="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 14: Badge Salida Centrado
'    <xf numFmtId="0" fontId="11" fillId="6" borderId="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 15: Totales Label Right
'    <xf numFmtId="164" fontId="4" fillId="6" borderId="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 16: Totales Balance Positive Right
'    <xf numFmtId="164" fontId="5" fillId="6" borderId="1" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>' . "\n" . // 17: Totales Balance Negative Right
'    <xf numFmtId="0" fontId="0" fillId="6" borderId="1" applyAlignment="1"><alignment vertical="center"/></xf>' . "\n" . // 18: Totales Empty Td
'  </cellXfs>' . "\n" .
'</styleSheet>';
    }

    private static function getContentTypesXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n" .
'  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n" .
'  <Default Extension="xml" ContentType="application/xml"/>' . "\n" .
'  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n" .
'  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n" .
'  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n" .
'</Types>';
    }

    private static function getRelsXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n" .
'  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' . "\n" .
'</Relationships>';
    }

    private static function getWorkbookRelsXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n" .
'  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' . "\n" .
'  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' . "\n" .
'</Relationships>';
    }

    private static function getWorkbookXml(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n" .
'  <sheets>' . "\n" .
'    <sheet name="Contabilidad" sheetId="1" r:id="rId1"/>' . "\n" .
'  </sheets>' . "\n" .
'</workbook>';
    }

    /**
     * Fallback a formato HTML/XML Spreadsheet con diseño y colores idénticos
     */
    public static function outputHtmlSpreadsheetFallback(array $movimientos, array $totales, array $filters, string $filename = 'Contabilidad_MySuite.xls'): void {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $periodo = (!empty($filters['mes']) && isset($meses[$filters['mes']]) ? $meses[$filters['mes']] . ' ' : '') . (!empty($filters['anio']) ? $filters['anio'] : 'Histórico completo');
        $tipoFiltro = !empty($filters['tipo']) ? strtoupper($filters['tipo']) : 'TODOS';

        $totales['total_entradas'] = (float)($totales['total_entradas'] ?? 0);
        $totales['total_salidas'] = (float)($totales['total_salidas'] ?? 0);
        $totales['balance_neto'] = (float)($totales['balance_neto'] ?? ($totales['total_entradas'] - $totales['total_salidas']));
        $totales['total_registros'] = (int)($totales['total_registros'] ?? count($movimientos));

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        echo "\xEF\xBB\xBF";
        ?>
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <!--[if gte mso 9]>
            <xml>
             <x:ExcelWorkbook>
              <x:ExcelWorksheets>
               <x:ExcelWorksheet>
                <x:Name>Movimientos</x:Name>
                <x:WorksheetOptions>
                 <x:DisplayGridlines/>
                </x:WorksheetOptions>
               </x:ExcelWorksheet>
              </x:ExcelWorksheets>
             </x:ExcelWorkbook>
            </xml>
            <![endif]-->
            <style>
                body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
                .header-title { font-size: 16pt; font-weight: bold; color: #1e3a8a; text-align: center; }
                .header-subtitle { font-size: 11pt; color: #475569; text-align: center; }
                th { background-color: #1e293b; color: #ffffff; font-weight: bold; border: 1px solid #94a3b8; text-align: center; padding: 8px; }
                td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: middle; }
                .entrada { color: #15803d; font-weight: bold; }
                .salida { color: #b91c1c; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .total-card { font-weight: bold; font-size: 12pt; background-color: #f8fafc; border: 2px solid #94a3b8; }
                .total-entrada { color: #15803d; font-size: 13pt; font-weight: bold; }
                .total-salida { color: #b91c1c; font-size: 13pt; font-weight: bold; }
                .total-balance { color: #1d4ed8; font-size: 14pt; font-weight: bold; }
            </style>
        </head>
        <body>
            <table border="0">
                <tr>
                    <td colspan="16" class="header-title">MY SUITE IN CARTAGENA - LIBRO DE CONTABILIDAD</td>
                </tr>
                <tr>
                    <td colspan="16" class="header-subtitle">
                        Período: <?= htmlspecialchars($periodo) ?>
                        | Tipo: <?= htmlspecialchars($tipoFiltro) ?>
                        <?= !empty($filters['caja']) ? ' | Caja: ' . htmlspecialchars(strtoupper($filters['caja'])) : '' ?>
                        | Generado el: <?= date('Y-m-d H:i:s') ?>
                    </td>
                </tr>
                <tr><td colspan="16"></td></tr>
                
                <!-- Resumen de Totales (5 Tarjetas) -->
                <tr>
                    <td colspan="3" class="total-card text-center">TOTAL ENTRADAS</td>
                    <td colspan="3" class="total-card text-center">TOTAL SALIDAS</td>
                    <td colspan="3" class="total-card text-center">BALANCE NETO</td>
                    <td colspan="3" class="total-card text-center">SALDO CAJA EVELYN</td>
                    <td colspan="4" class="total-card text-center">SALDO CAJA CARLOS</td>
                </tr>
                <tr>
                    <td colspan="3" class="total-card text-center total-entrada">$ <?= number_format($totales['total_entradas'], 2, ',', '.') ?></td>
                    <td colspan="3" class="total-card text-center total-salida">$ <?= number_format($totales['total_salidas'], 2, ',', '.') ?></td>
                    <td colspan="3" class="total-card text-center total-balance">$ <?= number_format($totales['balance_neto'], 2, ',', '.') ?></td>
                    <td colspan="3" class="total-card text-center total-entrada">$ <?= number_format((float)($totales['saldo_caja_evelyn'] ?? 0), 2, ',', '.') ?></td>
                    <td colspan="4" class="total-card text-center total-balance">$ <?= number_format((float)($totales['saldo_caja_carlos'] ?? 0), 2, ',', '.') ?></td>
                </tr>
                <tr><td colspan="16"></td></tr>

                <!-- Encabezados de la Tabla -->
                <tr>
                    <th style="width: 60px;"># ID</th>
                    <th style="width: 100px;">Fecha</th>
                    <th style="width: 50px;">Día</th>
                    <th style="width: 90px;">Mes</th>
                    <th style="width: 60px;">Año</th>
                    <th style="width: 90px;">Tipo</th>
                    <th style="width: 110px;">Caja</th>
                    <th style="width: 220px;">Categoría / Concepto</th>
                    <th style="width: 140px;">Monto (COP)</th>
                    <th style="width: 160px;">Método de Pago</th>
                    <th style="width: 160px;">Responsable</th>
                    <th style="width: 300px;">Descripción / Detalle</th>
                    <th style="width: 140px;"># Comprobante / Factura</th>
                    <th style="width: 90px;"># Reserva</th>
                    <th style="width: 150px;">Registrado Por</th>
                    <th style="width: 140px;">Fecha Registro</th>
                </tr>

                <!-- Filas de Datos -->
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="16" class="text-center" style="padding: 20px; color: #64748b;">
                            No hay movimientos registrados para el período o filtros seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                        <?php 
                            $tipo = strtolower($m['tipo'] ?? 'entrada');
                            $isEntrada = ($tipo === 'entrada');
                            $fecha = $m['fecha'] ?? date('Y-m-d');
                            $time = strtotime($fecha) ?: time();
                            $dia = $m['dia'] ?? (int)date('j', $time);
                            $mesNum = (int)($m['mes'] ?? date('n', $time));
                            $nombreMes = $meses[$mesNum] ?? ($m['mes'] ?? date('F', $time));
                            $anio = $m['anio'] ?? (int)date('Y', $time);
                            $idMov = $m['id_movimiento'] ?? ($m['id'] ?? '-');
                            $caja = (string)($m['caja'] ?? 'Evelyn');
                        ?>
                        <tr>
                            <td class="text-center"><?= htmlspecialchars((string)$idMov) ?></td>
                            <td class="text-center"><?= htmlspecialchars((string)$fecha) ?></td>
                            <td class="text-center"><?= htmlspecialchars((string)$dia) ?></td>
                            <td class="text-center"><?= htmlspecialchars((string)$nombreMes) ?></td>
                            <td class="text-center"><?= htmlspecialchars((string)$anio) ?></td>
                            <td class="text-center <?= $isEntrada ? 'entrada' : 'salida' ?>">
                                <?= strtoupper(htmlspecialchars((string)$tipo)) ?>
                            </td>
                            <td class="text-center">
                                <strong><?= htmlspecialchars($caja) ?></strong>
                            </td>
                            <td><?= htmlspecialchars((string)($m['categoria'] ?? '')) ?></td>
                            <td class="text-right <?= $isEntrada ? 'entrada' : 'salida' ?>">
                                <?= ($isEntrada ? '+ ' : '- ') . number_format((float)($m['monto'] ?? 0), 2, ',', '.') ?>
                            </td>
                            <td><?= htmlspecialchars((string)($m['metodo_pago'] ?? 'EFECTIVO')) ?></td>
                            <td><?= htmlspecialchars((string)($m['responsable'] ?? 'No especificado')) ?></td>
                            <td><?= htmlspecialchars((string)($m['descripcion'] ?? '')) ?></td>
                            <td class="text-center"><?= htmlspecialchars((string)($m['comprobante_numero'] ?? '-')) ?></td>
                            <td class="text-center"><?= htmlspecialchars((string)($m['id_reserva'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars((string)($m['creado_por'] ?? 'Sistema')) ?></td>
                            <td class="text-center"><?= htmlspecialchars((string)($m['creado_en'] ?? '-')) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Fila de Totales al final -->
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="8" class="text-right" style="padding: 10px;">TOTALES:</td>
                        <td class="text-right <?= $totales['balance_neto'] >= 0 ? 'entrada' : 'salida' ?>">
                            $ <?= number_format($totales['balance_neto'], 2, ',', '.') ?>
                        </td>
                        <td colspan="7"></td>
                    </tr>
                <?php endif; ?>
            </table>
        </body>
        </html>
        <?php
        exit;
    }
}
