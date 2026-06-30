import Alpine from 'alpinejs';
import Collapse from '@alpinejs/collapse';
import EditorJS from '@editorjs/editorjs';
import QRCode from 'qrcode';
import Sortable from 'sortablejs';
import TomSelect from 'tom-select';
import { Html5Qrcode, Html5QrcodeScanner } from 'html5-qrcode';

Alpine.plugin(Collapse);
window.Alpine = Alpine;
Alpine.start();

window.EditorJS = EditorJS;
window.QRCode = QRCode;
window.Sortable = Sortable;
window.TomSelect = TomSelect;
window.Html5Qrcode = Html5Qrcode;
window.Html5QrcodeScanner = Html5QrcodeScanner;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-editorjs]').forEach((node) => {
        const config = JSON.parse(node.dataset.editorjs || '{}');
        if (!config.holder) {
            config.holder = node.id;
        }

        new EditorJS({
            holder: config.holder,
            placeholder: config.placeholder || 'Start writing...',
            data: config.data || { blocks: [] },
            minHeight: 320,
        });
    });
});
