// Melhorias progressivas do Takin Petz (o site funciona sem JavaScript).
(function () {
  var base = document.body.getAttribute('data-base') || '/';

  // confirmação antes de ações destrutivas: <form data-confirm="texto">
  document.addEventListener('submit', function (ev) {
    var msg = ev.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) { ev.preventDefault(); }
  });

  // cidades por estado (IBGE, via /api/cidades/UF)
  var uf = document.getElementById('uf');
  var city = document.getElementById('city_id');
  var fallback = document.getElementById('city-fallback');
  if (uf && city) {
    var selectedCity = city.getAttribute('data-selected') || '';
    var loadCities = function () {
      city.disabled = true;
      city.innerHTML = '<option value="">Carregando cidades...</option>';
      fetch(base + 'api/cidades/' + encodeURIComponent(uf.value), { headers: { Accept: 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (list) {
          if (!list.length) { throw new Error('empty'); }
          city.innerHTML = '<option value="">Selecione a cidade</option>' + list.map(function (c) {
            return '<option value="' + c.id + '"' + (String(c.id) === selectedCity ? ' selected' : '') + '>' + c.name.replace(/</g, '&lt;') + '</option>';
          }).join('');
          city.disabled = false; city.hidden = false;
          if (fallback) { fallback.hidden = true; }
          selectedCity = '';
        })
        .catch(function () {
          city.hidden = true; city.disabled = true;
          if (fallback) { fallback.hidden = false; }
        });
    };
    uf.addEventListener('change', function () { selectedCity = ''; loadCities(); });
    loadCities();
  }

  // raças filtradas pela espécie escolhida
  var species = document.getElementById('especie');
  var breed = document.getElementById('raca_id');
  if (species && breed) {
    var filterBreeds = function () {
      Array.prototype.forEach.call(breed.options, function (opt) {
        var s = opt.getAttribute('data-species');
        var hide = !!s && !!species.value && s !== species.value;
        opt.hidden = hide; opt.disabled = hide;
      });
      var cur = breed.options[breed.selectedIndex];
      if (cur && cur.disabled) { breed.value = ''; }
    };
    species.addEventListener('change', filterBreeds);
    filterBreeds();
  }
})();
