import './bootstrap';

import Alpine from 'alpinejs';
import { createPopper } from '@popperjs/core';

window.Alpine = Alpine;
window.createPopper = createPopper;

Alpine.start();
