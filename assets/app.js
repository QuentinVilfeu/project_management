// Ensure jQuery is globally available for legacy plugins like DataTables
import $ from 'jquery';
window.$ = window.jQuery = $;
global.$ = global.jQuery = $;

import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
