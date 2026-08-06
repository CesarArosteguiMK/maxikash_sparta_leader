const test = require('node:test');
const assert = require('node:assert/strict');

const {
  TRUNCAR_AUTO_END_MINUTE,
  esVentanaTruncarMartesCdmx,
  parseHoraServidorCdmx,
} = require('../lib/truncarSchedule');

test('la ventana de recuperacion cubre el martes de 07:00 a 09:29', () => {
  assert.equal(TRUNCAR_AUTO_END_MINUTE, 570);
  assert.equal(esVentanaTruncarMartesCdmx({ fecha: '2026-08-04', horaSeg: '06:59:59' }), false);
  assert.equal(esVentanaTruncarMartesCdmx({ fecha: '2026-08-04', horaSeg: '07:00:00' }), true);
  assert.equal(esVentanaTruncarMartesCdmx({ fecha: '2026-08-04', horaSeg: '09:29:59' }), true);
  assert.equal(esVentanaTruncarMartesCdmx({ fecha: '2026-08-04', horaSeg: '09:30:00' }), false);
  assert.equal(esVentanaTruncarMartesCdmx({ fecha: '2026-08-05', horaSeg: '07:00:00' }), false);
});

test('interpreta la hora autoritativa devuelta por PHP', () => {
  const parsed = parseHoraServidorCdmx('2026-08-04 07:06:07 CST');
  assert.equal(parsed.fecha, '2026-08-04');
  assert.equal(parsed.hora, '07:06');
  assert.equal(parsed.horaSeg, '07:06:07');
  assert.equal(parsed.source, 'php-servidor');
});

test('rechaza una fecha imposible devuelta por PHP', () => {
  assert.throws(() => parseHoraServidorCdmx('2026-02-31 07:00:00 CST'), /fecha CDMX imposible/);
});
