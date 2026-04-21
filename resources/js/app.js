import { Html5Qrcode } from "html5-qrcode";
window.Html5Qrcode = Html5Qrcode;

import { Chart, registerables } from "chart.js";
Chart.register(...registerables);
window.Chart = Chart;
