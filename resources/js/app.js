import './bootstrap';

import Alpine from 'alpinejs';

// Trix — the lightweight rich-text editor used for the product
// description field in the admin panel (see resources/views/admin/
// products/_form.blade.php). Importing it registers the <trix-editor>
// custom element and wires it to its backing <input type="hidden">.
import 'trix';
import 'trix/dist/trix.css';

window.Alpine = Alpine;

Alpine.start();
