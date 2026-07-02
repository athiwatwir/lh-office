import './bootstrap';
import './components/property-images';
import './components/user-sortable';
import './components/article-sortable';
import './components/property-form-guard';

import Alpine from 'alpinejs';
import { createPopper } from '@popperjs/core';

window.Alpine = Alpine;
window.createPopper = createPopper;

Alpine.start();
