import './bootstrap';
import 'flyonui/flyonui';
import 'notyf/notyf.min.css';
import { Notyf } from 'notyf';

window.notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    dismissible: true,
    types: [
        {
            type: 'info',
            background: '#3ABFF8', 
            icon: {
                className: 'icon-[tabler--info-circle]',
                tagName: 'i',
            },
        },
    ],
});