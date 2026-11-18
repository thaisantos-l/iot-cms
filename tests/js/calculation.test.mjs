import test from 'node:test';
import assert from 'node:assert/strict';
import { calculate } from '../../wp-content/themes/iotnest/assets/src/js/lib/calculation.js';
const input={rate:200,hours:22,days:22,atual:60,iot:80};
test('OEE: planilha, extremos e meta igual',()=>{
  const value=calculate(input);assert.equal(value.total,96800);assert.equal(value.atual.loss,38720);assert.ok(Math.abs(value.iot.loss-19360)<0.00001);
  assert.equal(calculate({...input,atual:0,iot:100}).atual.loss,96800);assert.equal(calculate({...input,atual:0,iot:100}).iot.loss,0);
  assert.deepEqual(calculate({...input,iot:60}).atual,calculate({...input,iot:60}).iot);
  assert.equal(calculate({...input,rate:'200,50'}).total,97042);
});
test('OEE: vazios, não finitos, limites e meta menor',()=>{
  for(const value of ['', ' ', null, undefined, 'NaN',Infinity]) assert.equal(calculate({...input,atual:value}).error,'invalid');
  for(const [field,value] of [['rate',0],['hours',25],['days',0],['days',32],['days',1.5],['atual',-1],['iot',101],['rate',Number.MAX_VALUE]]) assert.ok(calculate({...input,[field]:value}).error);
  assert.equal(calculate({...input,iot:59}).error,'target');
});
