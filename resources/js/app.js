import './bootstrap';
import './components/property-images';
import './components/user-sortable';

import Alpine from 'alpinejs';
import { createPopper } from '@popperjs/core';

window.Alpine = Alpine;
window.createPopper = createPopper;

Alpine.start();
