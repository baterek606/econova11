document.addEventListener('DOMContentLoaded', () => {
  let currentCategory = 'all';

  // Initialize
  fetchGoal();
  fetchExplorePosts(currentCategory);

  // Setup filters
  const filterBtns = document.querySelectorAll('.filter-btn');
  filterBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      // Remove active class from all
      filterBtns.forEach(b => b.classList.remove('active'));
      // Add active class to clicked
      const targetBtn = e.currentTarget;
      targetBtn.classList.add('active');
      
      // Fetch new category
      currentCategory = targetBtn.dataset.category;
      fetchExplorePosts(currentCategory);
    });
  });

});

async function fetchGoal() {
  try {
    const res = await fetch('http://localhost:3000/api/explore/goal');
    if (!res.ok) throw new Error('Failed to fetch goal');
    const goal = await res.json();
    renderGoal(goal);
  } catch (err) {
    console.error('Error fetching goal:', err);
    document.getElementById('goalTitle').textContent = 'Could not load goal.';
  }
}

function renderGoal(goal) {
  if (!goal) return;
  document.getElementById('goalTitle').textContent = goal.title;
  document.getElementById('goalCurrent').textContent = `${goal.current_amount.toLocaleString()} planted`;
  
  const percent = Math.round((goal.current_amount / goal.target_amount) * 100);
  document.getElementById('goalPercent').textContent = `${percent}%`;
  document.getElementById('goalProgress').style.width = `${percent}%`;
}

async function fetchExplorePosts(category) {
  try {
    const res = await fetch(`http://localhost:3000/api/explore/posts?category=${category}`);
    if (!res.ok) throw new Error('Failed to fetch posts');
    const posts = await res.json();
    renderExplorePosts(posts);
  } catch (err) {
    console.error('Error fetching posts:', err);
    document.getElementById('explorePostsContainer').innerHTML = '<p>Error loading impact stories.</p>';
  }
}

function renderExplorePosts(posts) {
  const container = document.getElementById('explorePostsContainer');
  if (!posts || posts.length === 0) {
    container.innerHTML = '<p style="color: var(--text-muted);">No impact stories found for this ecosystem.</p>';
    return;
  }

  container.innerHTML = posts.map(p => `
    <div class="card explore-card">
      <div class="slider-container" id="slider-${p.id}">
        <span class="slider-badge" id="badge-${p.id}">BEFORE</span>
        
        <img src="${p.before_image}" class="slider-img" id="img-before-${p.id}" alt="Before">
        <img src="${p.after_image}" class="slider-img hidden" id="img-after-${p.id}" alt="After">

        <div class="slider-arrow left" onclick="toggleSlider(${p.id}, 'before')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </div>
        <div class="slider-arrow right" onclick="toggleSlider(${p.id}, 'after')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </div>
      </div>

      <div class="card-content">
        <div class="user-meta" style="margin-bottom: 12px;">
          <div class="avatar" style="background-image: url('https://i.pravatar.cc/100?u=${p.user_name}');"></div>
          <div>
            <strong>${p.user_name}</strong>
            <span class="time">${p.user_location}</span>
          </div>
        </div>

        <h4>${p.title}</h4>
        <p>${p.description}</p>

        <div class="feed-actions">
          <div class="stats">
            <span style="cursor:pointer" onclick="likeExplorePost(${p.id})">♡ <span id="like-count-${p.id}" data-raw="${p.likes_count}">${formatNumber(p.likes_count)}</span></span>
            <span style="cursor:pointer" onclick="commentExplorePost(${p.id})">💬 <span id="comment-count-${p.id}" data-raw="${p.comments_count}">${formatNumber(p.comments_count)}</span></span>
          </div>
          <span class="badge small" style="margin: 0; background-color: var(--bg-badge); color: var(--text-green);">#${p.category.toUpperCase()}</span>
        </div>
      </div>
    </div>
  `).join('');
}

// Global scope functions for inline onclick handlers
window.toggleSlider = function(postId, target) {
  const imgBefore = document.getElementById(`img-before-${postId}`);
  const imgAfter = document.getElementById(`img-after-${postId}`);
  const badge = document.getElementById(`badge-${postId}`);

  if (target === 'after') {
    imgBefore.classList.add('hidden');
    imgAfter.classList.remove('hidden');
    badge.textContent = 'AFTER';
  } else {
    imgAfter.classList.add('hidden');
    imgBefore.classList.remove('hidden');
    badge.textContent = 'BEFORE';
  }
};

window.likeExplorePost = async function(id) {
  try {
    const res = await fetch(`http://localhost:3000/api/explore/posts/${id}/like`, { method: 'POST' });
    if (res.ok) {
      const el = document.getElementById(`like-count-${id}`);
      let count = parseInt(el.getAttribute('data-raw'), 10);
      count++;
      el.setAttribute('data-raw', count);
      el.textContent = formatNumber(count);
    }
  } catch (err) {
    console.error('Error liking post', err);
  }
};

window.commentExplorePost = async function(id) {
  try {
    const res = await fetch(`http://localhost:3000/api/explore/posts/${id}/comment`, { method: 'POST' });
    if (res.ok) {
      const el = document.getElementById(`comment-count-${id}`);
      let count = parseInt(el.getAttribute('data-raw'), 10);
      count++;
      el.setAttribute('data-raw', count);
      el.textContent = formatNumber(count);
    }
  } catch (err) {
    console.error('Error commenting', err);
  }
};

function formatNumber(num) {
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'k';
  }
  return num;
}
