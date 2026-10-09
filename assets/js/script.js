const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#main-navigation');

function closeMenu() {
  menuButton?.setAttribute('aria-expanded', 'false');
  menuButton?.setAttribute('aria-label', 'Open navigation menu');
  navigation?.classList.remove('is-open');
}

menuButton?.addEventListener('click', () => {
  const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
  menuButton.setAttribute('aria-expanded', String(!isOpen));
  menuButton.setAttribute('aria-label', isOpen ? 'Open navigation menu' : 'Close navigation menu');
  navigation?.classList.toggle('is-open', !isOpen);
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') closeMenu();
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

document.querySelectorAll('[data-ingredients]').forEach((list) => {
  const addButton = document.querySelector('[data-add-ingredient]');
  const template = () => {
    const row = document.createElement('div');
    row.className = 'ingredient-row';
    row.innerHTML = '<input type="text" name="ingredients[]" maxlength="255" placeholder="e.g. 1 cup coconut milk" required><button class="icon-button remove-ingredient" type="button" aria-label="Remove ingredient">×</button>';
    return row;
  };
  addButton?.addEventListener('click', () => list.appendChild(template()));
  list.addEventListener('click', (event) => {
    const removeButton = event.target.closest('.remove-ingredient');
    if (!removeButton) return;
    const rows = list.querySelectorAll('.ingredient-row');
    if (rows.length > 1) removeButton.closest('.ingredient-row').remove();
  });
});

async function toggleFavorite(button) {
  if (button.dataset.loading === 'true') return;
  button.dataset.loading = 'true';
  button.disabled = true;
  button.classList.add('is-loading');
  try {
    const response = await fetch('api/toggle_favorite.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
      body: new URLSearchParams({ recipe_id: button.dataset.favoriteId, csrf_token: csrfToken }),
    });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || 'Could not update saved recipes.');
    button.classList.toggle('is-favorited', data.is_favorited);
    button.setAttribute('aria-pressed', String(data.is_favorited));
    button.setAttribute('aria-label', data.is_favorited ? 'Remove from saved recipes' : 'Save recipe');
    button.querySelector('.favorite-text').textContent = data.is_favorited ? 'Saved' : 'Save';
  } catch (error) {
    window.alert(error.message);
  } finally {
    button.dataset.loading = 'false';
    button.disabled = false;
    button.classList.remove('is-loading');
  }
}

document.querySelectorAll('[data-favorite-id]').forEach((button) => {
  button.addEventListener('click', () => toggleFavorite(button));
});
