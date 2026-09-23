document.addEventListener('DOMContentLoaded', function () {
  const notifBtn = document.getElementById('notifBtn');
  const notifDropdown = document.getElementById('notifDropdown');
  const notifList = document.getElementById('notifList');
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.querySelector('.nav-links');

  if (hamburger) {
    hamburger.addEventListener('click', function () {
      navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
      navLinks.style.flexDirection = 'column';
      navLinks.style.position = 'absolute';
      navLinks.style.top = '64px';
      navLinks.style.left = '0';
      navLinks.style.right = '0';
      navLinks.style.background = '#171e30';
      navLinks.style.padding = '10px 20px';
    });
  }

  if (notifBtn) {
    const base = window.IS_ADMIN_PAGE ? '../' : '';

    async function loadNotifications() {
      notifList.innerHTML = '<div class="notif-empty">Loading…</div>';
      try {
        const res = await fetch(base + 'api_notifications.php');
        const data = await res.json();
        if (!data.notifications || data.notifications.length === 0) {
          notifList.innerHTML = '<div class="notif-empty">No notifications yet 🎉</div>';
          return;
        }
        notifList.innerHTML = data.notifications.map(n => `
          <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}">
            <div>${n.message}</div>
            <span class="notif-time">${n.type} · ${n.time_ago}</span>
          </div>
        `).join('');
      } catch (e) {
        notifList.innerHTML = '<div class="notif-empty">Could not load notifications</div>';
      }
    }

    notifBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      const isOpen = notifDropdown.classList.toggle('open');
      if (isOpen) {
        loadNotifications();
        fetch(base + 'api_notifications.php?mark_read=1');
        const badge = notifBtn.querySelector('.badge');
        if (badge) badge.remove();
      }
    });

    document.addEventListener('click', function (e) {
      if (!notifDropdown.contains(e.target) && e.target !== notifBtn) {
        notifDropdown.classList.remove('open');
      }
    });
  }

  // Confirm dialogs for destructive actions
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // Image preview on file input (supports multiple photos)
  const fileInput = document.getElementById('itemImage');
  if (fileInput) {
    fileInput.addEventListener('change', function () {
      const previewWrap = document.getElementById('imgPreviewWrap');
      const label = document.getElementById('fileLabel');
      if (!previewWrap) return;
      previewWrap.innerHTML = '';
      const files = Array.from(this.files || []);
      files.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
          const img = document.createElement('img');
          img.src = e.target.result;
          previewWrap.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
      if (label) label.textContent = files.length ? `${files.length} photo(s) selected` : '📷 Click to upload photos';
    });
  }

  // Item detail page: gallery thumbnails + lightbox (click image to enlarge)
  const mainImg = document.getElementById('mainItemImage');
  const lightbox = document.getElementById('imgLightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxClose = document.getElementById('lightboxClose');

  function openLightbox(src) {
    if (!lightbox || !lightboxImg) return;
    lightboxImg.src = src;
    lightbox.classList.add('open');
  }

  if (mainImg) {
    mainImg.addEventListener('click', () => openLightbox(mainImg.src));
  }

  document.querySelectorAll('.gallery-thumb').forEach(thumb => {
    thumb.addEventListener('click', function () {
      document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      if (mainImg) mainImg.src = this.dataset.full;
    });
  });

  if (lightbox) {
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox || e.target === lightboxClose) {
        lightbox.classList.remove('open');
      }
    });
  }

  // Live Instant Search & Filtering
  const searchInput = document.getElementById('browseSearchInput');
  const catSelect = document.getElementById('browseCategorySelect');
  const clearBtn = document.getElementById('clearSearchBtn');
  const resetBtn = document.getElementById('resetSearchBtn');
  const itemsGrid = document.getElementById('itemsGrid');
  const noMatchState = document.getElementById('noMatchState');
  const searchKeyword = document.getElementById('searchKeyword');

  if (searchInput && itemsGrid) {
    const itemCards = Array.from(itemsGrid.querySelectorAll('.item-card'));

    function performLiveFilter() {
      const query = (searchInput.value || '').trim().toLowerCase();
      const selectedCat = (catSelect ? catSelect.value : '').trim().toLowerCase();

      // Show or hide clear button
      if (clearBtn) {
        clearBtn.style.display = query.length > 0 ? 'flex' : 'none';
      }

      let visibleCount = 0;

      itemCards.forEach(function (card) {
        const name = card.dataset.name || '';
        const desc = card.dataset.desc || '';
        const cat = card.dataset.cat || '';
        const owner = card.dataset.owner || '';

        const matchesQuery = query === '' || 
          name.includes(query) || 
          desc.includes(query) || 
          cat.includes(query) || 
          owner.includes(query);

        const matchesCat = selectedCat === '' || cat === selectedCat;

        if (matchesQuery && matchesCat) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      if (noMatchState) {
        if (visibleCount === 0 && itemCards.length > 0) {
          noMatchState.style.display = 'block';
          if (searchKeyword) {
            searchKeyword.textContent = searchInput.value.trim() || selectedCat;
          }
        } else {
          noMatchState.style.display = 'none';
        }
      }
    }

    // Trigger on typing each relative letter
    searchInput.addEventListener('input', performLiveFilter);
    searchInput.addEventListener('search', performLiveFilter);

    // Trigger on category select change
    if (catSelect) {
      catSelect.addEventListener('change', performLiveFilter);
    }

    // Clear search handler - resets input and instantly displays all items
    function clearSearch() {
      searchInput.value = '';
      if (catSelect) catSelect.value = '';
      performLiveFilter();
      searchInput.focus();
    }

    if (clearBtn) {
      clearBtn.addEventListener('click', clearSearch);
    }
    if (resetBtn) {
      resetBtn.addEventListener('click', clearSearch);
    }

    // Run initial filter if value exists on load
    if (searchInput.value || (catSelect && catSelect.value)) {
      performLiveFilter();
    }
  }
});
