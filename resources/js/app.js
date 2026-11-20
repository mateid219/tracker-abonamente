import Alpine from 'alpinejs';
import './echo';
import { startRealtime } from './realtime';
import subscriptionsDashboard from './dashboard';

window.Alpine = Alpine;

Alpine.data('subscriptionsDashboard', subscriptionsDashboard);

// Listen first, so components see the WebSocket events as soon as they are created.
startRealtime();

Alpine.start();
