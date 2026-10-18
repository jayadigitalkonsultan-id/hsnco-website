(() => {
  'use strict';
  const element = document.getElementById('adminChartData');
  const canvas = document.getElementById('editorialCanvas');
  if (!element || !canvas || !window.Chart) return;
  const data = JSON.parse(element.textContent);
  const statuses = ['draft', 'review', 'published', 'archived'];
  const labels = ['Draft', 'Perlu review', 'Tayang', 'Diarsipkan'];
  const values = statuses.map(status => Number(data[status] || 0));
  if (!values.some(value => value > 0)) return;
  canvas.setAttribute('aria-label', labels.map((label, index) => `${label}: ${values[index]}`).join(', '));
  const chart = new Chart(canvas, {
    type: 'doughnut',
    data: {labels, datasets: [{data: values, backgroundColor: ['#a89980', '#b69b62', '#607f69', '#7d858a'], borderWidth: 3, borderColor: '#fff', hoverOffset: 4}]},
    options: {responsive: true, maintainAspectRatio: false, cutout: '75%', animation: matchMedia('(prefers-reduced-motion:reduce)').matches ? false : {duration: 300}, plugins: {legend: {display: false}}}
  });
  document.getElementById('editorialCanvasWrap').hidden = false;
  window.addEventListener('pagehide', () => chart.destroy(), {once: true});
})();
