(function () {
  var radios = document.querySelectorAll('input[name="paymethod"]');
  function update() {
    document.querySelectorAll('.pay-option').forEach(function (el) {
      el.classList.remove('active');
    });
    var checked = document.querySelector('input[name="paymethod"]:checked');
    if (checked) {
      var card = checked.closest('.pay-option');
      if (card) card.classList.add('active');
    }
  }
  radios.forEach(function (r) { r.addEventListener('change', update); });
  update();

  // Coupon toggle
  var link = document.querySelector('[data-coupon-toggle]');
  var box = document.getElementById('couponCollapse');
  if (link && box) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      box.classList.toggle('show');
      if (box.classList.contains('show')) {
        var input = box.querySelector('input'); if (input) input.focus();
      }
    });
  }
})();
