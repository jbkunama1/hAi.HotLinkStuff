const buttons = document.querySelectorAll('[data-tab]');
const panels = {
  hotstuff: document.getElementById('panel-hotstuff'),
  promptsave: document.getElementById('panel-promptsave')
};

function activateTab(tab) {
  buttons.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tab));
  Object.entries(panels).forEach(([key, panel]) => {
    panel.classList.toggle('active', key === tab);
  });
}

buttons.forEach(btn => {
  btn.addEventListener('click', () => activateTab(btn.dataset.tab));
});
