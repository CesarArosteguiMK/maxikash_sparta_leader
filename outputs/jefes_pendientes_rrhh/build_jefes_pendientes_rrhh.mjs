import fs from "node:fs/promises";
import { SpreadsheetFile, Workbook } from "@oai/artifact-tool";

const resumen = [
  ["Texto en Excel", "Registros", "Motivo", "Accion sugerida", "Jefe correcto / reemplazo", "Notas RR.HH."],
  ["PRESIDENTE DE CONSEJO DE ADMINISTRACION", 2, "No existe como persona/jefe en el sistema", "Definir si queda sin jefe o capturar persona responsable", "", ""],
  ["ARMANDO AGUIRRE", 1, "No encontrado como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["ENRIQUE POOT", 1, "No encontrado como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["ALDO ESCOBAR", 1, "No encontrado como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["FERNANDO BERMUDEZ", 4, "No encontrado como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["JOSE GERARDO VALADEZ", 3, "No encontrado como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["HUBER PEREZ", 1, "No encontrado como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["JUAN CARLOS ROQUE", 1, "No encontrado como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["CRISTINA ANDRADE", 1, "No encontrado como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["MAGDIEL", 1, "Nombre incompleto", "Indicar nombre completo del jefe", "", ""],
  ["MARIA DEL ROSARIO CARRANZA BARRIOS", 1, "No encontrada como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["RAMON OLIVA", 1, "No encontrado como jefe activo", "Indicar jefe actual o confirmar sin reemplazo", "", ""],
  ["JULIO ALVARADO", 1, "No encontrado como jefe activo", "Confirmado: no existe como jefe; indicar reemplazo si aplica", "", ""],
  ["IVAN RAFAEL HERNANDEZ", 1, "No encontrado como jefe activo", "Confirmado: no existe como jefe; indicar reemplazo si aplica", "", ""],
  ["NAYELI AGUILAR", 7, "No encontrada como jefe activo ni en la plantilla revisada", "Confirmado: no se localiza; indicar reemplazo si aplica", "", ""],
];

const detalle = [
  ["Texto jefe en Excel", "Colaborador afectado", "Numero empleado / origen", "Direccion", "Motivo", "Jefe correcto / reemplazo", "Notas RR.HH."],
  ["PRESIDENTE DE CONSEJO DE ADMINISTRACION", "ALEJANDRO ROMULO PAEZ EUSEBIO", "ID sistema 1360", "DIRECCION GENERAL", "Jefe no resuelto", "", ""],
  ["PRESIDENTE DE CONSEJO DE ADMINISTRACION", "MOISES FRIEDMAN HUBER", "ID sistema 1186", "DIRECCION GENERAL", "Jefe no resuelto", "", ""],
  ["EDUARDO VELAZCO", "JESUS ORTIZ LUCERO", "Persona del Excel no encontrada", "COMERCIAL", "No se pudo empatar la persona del Excel con el sistema", "", ""],
  ["ARMANDO AGUIRRE", "MARA GABRIELA VIAMONTE ROSALES", "ID sistema 1238", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["JUAN CARLOS MARTINEZ SOTO", "MARCELO JORGE ARCOS DE LA CRUZ", "Persona del Excel no encontrada", "AUTOMATIZACION E INNOVACION IA", "No se pudo empatar la persona del Excel con el sistema", "", ""],
  ["ENRIQUE POOT", "GUADALUPE DEL CARPIO CASTELLANOS", "ID sistema 1243", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["ALDO ESCOBAR", "ISRAEL CASTILLO MENDOZA", "ID sistema 1368", "ADMINISTRACION Y FINANZAS", "Jefe no resuelto", "", ""],
  ["FERNANDO BERMUDEZ", "GERMAN MEJIA AGUILAR", "ID sistema 1370", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["JOSE GERARDO VALADEZ", "ABEL HERNANDEZ CAMACHO", "ID sistema 1256", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["HUBER PEREZ", "LUIS DAVID GUTIERREZ MORALES", "ID sistema 1258", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["JUAN CARLOS ROQUE", "MIRYAM ANAHY SILVA VALERO", "ID sistema 1379", "MARKETING", "Jefe no resuelto", "", ""],
  ["JOSE GERARDO VALADEZ", "LUIS GUSTAVO ROMO MENDOZA", "ID sistema 1259", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["JOSE GERARDO VALADEZ", "DIANA ISABEL JAQUEZ NUNEZ", "ID sistema 1261", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["FERNANDO BERMUDEZ", "CARLOS DAVID BERMUDEZ ALDRETE", "ID sistema 1384", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["FERNANDO BERMUDEZ", "MARIELLE LOPEZ CASTREJON", "ID sistema 1389", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["CRISTINA ANDRADE", "ANDRES ROMERO NAJERA", "ID sistema 1269", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["MAGDIEL", "FRIDA XIMENA SEGURA CADENA", "ID sistema 1271", "TI", "Jefe no resuelto por nombre incompleto", "", ""],
  ["GUSTAVO MENDOZA", "LUIS JOSE SANCHEZ VILLEGAS", "Persona ambigua", "COMERCIAL", "La persona del Excel tiene mas de una coincidencia posible", "", ""],
  ["MARIA DEL ROSARIO CARRANZA BARRIOS", "BELSY ADRIANA CABRERA RIOS", "ID sistema 1285", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["FERNANDO BERMUDEZ", "MARLENE BOLANOS ZAVALA", "ID sistema 1401", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["CESAR OMAR CASAS", "GAEL ANTONIO VEGA TREJO", "Persona ambigua", "COMERCIAL", "La persona del Excel tiene mas de una coincidencia posible", "", ""],
  ["RAMON OLIVA", "IRMA NALLELY AGUILAR ISLAS", "ID sistema 1440", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["JULIO ALVARADO", "JUAN CARLOS REYNOSA VILLALTA", "ID sistema 1315", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["IVAN RAFAEL HERNANDEZ", "SALVADOR MEDEL MIRANDA", "ID sistema 1316", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["NAYELI AGUILAR", "CESAR VILLEGAS PONCE", "ID sistema 1466", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["NAYELI AGUILAR", "JUAN ANTONIO GARCIA REYES", "ID sistema 1318", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["NAYELI AGUILAR", "JOSE ANTONIO MENESES GONZALEZ", "ID sistema 1319", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["NAYELI AGUILAR", "SALVADOR GONZALEZ CABRERA", "ID sistema 1320", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["NAYELI AGUILAR", "DANIEL SALMERON ACOSTA", "ID sistema 1321", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["NAYELI AGUILAR", "EDUARDO DANIEL MORALES MIRANDA", "ID sistema 1468", "COMERCIAL", "Jefe no resuelto", "", ""],
  ["NAYELI AGUILAR", "STEPHANIA GUZMAN BASTIDA", "ID sistema 1322", "COMERCIAL", "Jefe no resuelto", "", ""],
];

const workbook = Workbook.create();
const resumenSheet = workbook.worksheets.add("Jefes pendientes");
const detalleSheet = workbook.worksheets.add("Detalle colaboradores");

function writeSheet(sheet, rows, widths) {
  const lastCol = String.fromCharCode(64 + rows[0].length);
  const tableRange = sheet.getRange(`A1:${lastCol}${rows.length}`);
  tableRange.values = rows;
  tableRange.format = {
    wrapText: true,
    font: { name: "Aptos", size: 10 },
    borders: { preset: "all", style: "thin", color: "#D9E2EC" },
  };
  const header = sheet.getRange(`A1:${lastCol}1`);
  header.format = {
    fill: "#1F3A5F",
    font: { color: "#FFFFFF", bold: true },
    wrapText: true,
  };
  sheet.freezePanes.freezeRows(1);
  widths.forEach((width, idx) => {
    const col = String.fromCharCode(65 + idx);
    sheet.getRange(`${col}:${col}`).format.columnWidth = width;
  });
}

writeSheet(resumenSheet, resumen, [36, 10, 42, 52, 32, 34]);
writeSheet(detalleSheet, detalle, [34, 38, 26, 28, 44, 32, 34]);

const totalRegistros = resumen.slice(1).reduce((sum, row) => sum + Number(row[1] || 0), 0);
const notas = workbook.worksheets.add("Notas");
const notasRows = [
  ["Reporte", "Jefes pendientes por resolver"],
  ["Fecha", "2026-06-09"],
  ["Total textos de jefe pendientes", resumen.length - 1],
  ["Total registros afectados por jefe pendiente", totalRegistros],
  ["Criterio", "Solo se incluyen textos reales que aparecen en la columna de jefe del Excel y que no pudieron resolverse en el sistema."],
  ["Importante", "Los campos 'Jefe correcto / reemplazo' y 'Notas RR.HH.' quedan vacios para que RR.HH. capture la decision final."],
];
notas.getRange(`A1:B${notasRows.length}`).values = notasRows;
notas.getRange("A1:A6").format.font.bold = true;
notas.getRange("A1:B1").format.fill.color = "#DDEBFF";
notas.getRange("A1:B6").format.wrapText = true;
notas.getRange("A:A").format.columnWidth = 34;
notas.getRange("B:B").format.columnWidth = 90;

const errors = await workbook.inspect({
  kind: "match",
  searchTerm: "#REF!|#DIV/0!|#VALUE!|#NAME\\?|#N/A",
  options: { useRegex: true, maxResults: 50 },
  summary: "formula error scan",
});
console.log(errors.ndjson);

await workbook.render({ sheetName: "Jefes pendientes", range: "A1:F16", scale: 1 });
await workbook.render({ sheetName: "Detalle colaboradores", range: "A1:G20", scale: 1 });
await workbook.render({ sheetName: "Notas", range: "A1:B6", scale: 1 });

const outputPath = "C:/xampp/htdocs/sparta___SPARTA_SECRET_REDACTED__/outputs/jefes_pendientes_rrhh/jefes_pendientes_rrhh.xlsx";
await fs.mkdir("C:/xampp/htdocs/sparta___SPARTA_SECRET_REDACTED__/outputs/jefes_pendientes_rrhh", { recursive: true });
const output = await SpreadsheetFile.exportXlsx(workbook);
await output.save(outputPath);
console.log(outputPath);
