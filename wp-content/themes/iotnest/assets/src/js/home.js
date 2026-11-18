import { initializeComponents, onReady } from './lib/components.js';
import { initializeAI, initializeMES } from './components/tabs.js';
import { initializeHardware, initializeCarousel } from './components/carousel.js';
import { initializeSteps } from './components/steps.js';
import { initializeCalculator } from './components/calculator.js';
import { initializeEnergy, initializeHeroZoom, initializeDadaScroll, initializeCounter, initializeMarquee } from './components/motion.js';
import { initializeHeroVideo, initializeDadaVideo } from './components/media.js';
onReady(()=>initializeComponents(document,[
  {selector:'[data-ai-tabs]',initialize:initializeAI},{selector:'[data-mess]',initialize:initializeMES},
  {selector:'[data-hw]',initialize:initializeHardware},{selector:'[data-carousel]',initialize:initializeCarousel},
  {selector:'[data-how]',initialize:initializeSteps},{selector:'[data-calc]',initialize:initializeCalculator},
  {selector:'.energy-visual',initialize:initializeEnergy},{selector:'.hero',initialize:initializeHeroZoom},
  {selector:'[data-dada-frame]',initialize:initializeDadaScroll},{selector:'[data-count]',initialize:initializeCounter},
  {selector:'.marquee',initialize:initializeMarquee},{selector:'.hero',initialize:initializeHeroVideo},
  {selector:'[data-dada-frame]',initialize:initializeDadaVideo},
]));
