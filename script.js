document.addEventListener('DOMContentLoaded', () => {
  // Existing Toggle Logic
  const toggleBtn = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');
  if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener('click', () => {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      if (isPassword) {
        eyeIcon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`;
      } else {
        eyeIcon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
      }
    });
  }

  // Signup Logic
  const signupForm = document.getElementById('signupForm');
  if (signupForm) {
    signupForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('fullName').value;
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;

      if (!name || !email || !password) {
        return alert('Please fill in all fields');
      }

      const users = JSON.parse(localStorage.getItem('users') || '[]');
      if (users.find(u => u.email === email)) {
        return alert('Email already registered. Please log in.');
      }

      const user = { name, email, password, role: 'user' };
      users.push(user);
      localStorage.setItem('users', JSON.stringify(users));
      localStorage.setItem('user', JSON.stringify(user));
      window.location.href = 'index.html';
    });
  }

  // Login Logic
  const loginForm = document.getElementById('auth-form');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;

      if (!email || !password) {
        return alert('Please enter both email and password');
      }

      const users = JSON.parse(localStorage.getItem('users') || '[]');
      const user = users.find(u => u.email === email && u.password === password);
      
      if (!user) {
          return alert('Invalid email or password');
      }

      localStorage.setItem('user', JSON.stringify(user));
      window.location.href = 'index.html';
    });
  }

  // Fetching Data Logic for Home Page
  const postsContainer = document.getElementById('postsContainer');
  if (postsContainer) {
    fetchData();
  }

  // Explore Page Initialization
  const explorePostsContainer = document.getElementById('explorePostsContainer');
  if (explorePostsContainer) {
    renderGoalMock();
    renderExplorePosts(explorePosts);
    initFilters();
  }

  updateNavbar();
});

let currentUser = null;
try {
  const userStr = localStorage.getItem('user');
  if (userStr && userStr !== 'undefined') {
    currentUser = JSON.parse(userStr);
  }
} catch (e) {
  console.error('Error parsing user', e);
  localStorage.removeItem('user');
}
const isOwner = currentUser && currentUser.role === 'owner';

function updateNavbar() {
  if (!currentUser) return;
  
  const navContainer1 = document.querySelector('.nav');
  if (navContainer1 && !navContainer1.classList.contains('center-nav')) { // Don't replace index.html center nav
    navContainer1.innerHTML = `
      <span class="nav-link" style="font-weight: 600; margin-right: 15px; color: var(--text-main);">Hi, ${currentUser.name}</span>
      <button onclick="logout()" class="btn btn-outline btn-sm" style="padding: 6px 12px;">Logout</button>
    `;
  }
  
  const navContainer2 = document.querySelector('.nav-links');
  if (navContainer2) {
    navContainer2.innerHTML = `
      <span class="login-link" style="font-weight: 600; margin-right: 15px; color: var(--text-main);">Hi, ${currentUser.name}</span>
      <button onclick="logout()" class="join-btn" style="padding: 6px 12px; background: transparent; color: var(--text-main); border: 1px solid var(--text-main);">Logout</button>
    `;
  }
  
  const navActions = document.querySelector('.nav-actions');
  if (navActions) {
    const createBtn = navActions.querySelector('a.btn-dark');
    if (createBtn) {
      createBtn.outerHTML = `
        <span style="font-weight: 600; margin-right: 15px; color: var(--text-main);">Hi, ${currentUser.name}</span>
        <button onclick="logout()" class="btn btn-outline btn-sm">Logout</button>
      `;
    }
  }
}

window.logout = function() {
  localStorage.removeItem('user');
  window.location.href = 'login.html';
}

async function fetchData() {
  try {
    const [postsRes, campaignsRes, statsRes, stewardsRes] = await Promise.all([
      fetch('http://localhost:3000/api/posts'),
      fetch('http://localhost:3000/api/campaigns'),
      fetch('http://localhost:3000/api/stats'),
      fetch('http://localhost:3000/api/stewards')
    ]);

    const posts = await postsRes.json();
    const campaigns = await campaignsRes.json();
    const stats = await statsRes.json();
    const stewards = await stewardsRes.json();

    renderPosts(posts);
    renderCampaigns(campaigns);
    renderStats(stats);
    renderStewards(stewards);

    const statsContainer = document.getElementById('statsContainer');
    if (statsContainer) {
      const parentCol = statsContainer.closest('.feed-col');
      if (parentCol) {
        // Always show the COMMUNITY IMPACT column
        parentCol.style.display = 'block';
      }
    }

    if (isOwner) addOwnerControls();
  } catch (e) {
    console.error('Error fetching data', e);
  }
}

function renderPosts(posts) {
  const container = document.getElementById('postsContainer');
  if (!container) return;

  if (!posts || posts.length === 0) {
    container.parentElement.style.display = 'none';
    return;
  }

  container.parentElement.style.display = 'block';

  const p = posts[0];

  container.innerHTML = `
        <div class="card feed-card" style="position:relative">
            ${isOwner ? `<button onclick="deletePost(${p.id})" style="position:absolute; right:10px; top:10px; color:red; z-index:10;">🗑️</button>` : ''}
            ${p.type === 'REFORESTATION' ? `
            <div class="feed-card-img" style="background-image: url('https://images.unsplash.com/photo-1591193116044-f9dd3592e56d?auto=format&fit=crop&q=80&w=400');">
              <span class="badge white">${p.type}</span>
            </div>` : ''}
            <div class="card-content">
              <div class="user-meta">
                ${p.type === 'REFORESTATION' ?
      `<div class="avatar" style="background-image: url('https://i.pravatar.cc/100?img=5');"></div>` :
      `<div class="avatar icon-avatar"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4l3 3"></path></svg></div>`
    }
                <div>
                  <strong>${p.author_name}</strong>
                  ${p.time_ago ? `<span class="time">${p.time_ago} • ${p.location}</span>` : ''}
                </div>
                ${p.type === 'ARTICLE' ? `<span class="badge small" style="margin-left: auto;">ARTICLE</span>` : ''}
              </div>
              <h4 style="${p.type === 'ARTICLE' ? 'margin: 12px 0;' : ''}">${p.title}</h4>
              ${p.content ? `<p>${p.content}</p>` : ''}
              
              ${p.type === 'REFORESTATION' ? `
              <div class="feed-actions">
                <span class="green-circle">+15</span>
                <div class="stats">
                  <span style="cursor:pointer" onclick="likePost(${p.id})">♡ ${p.likes_count || 0}</span>
                  <span style="cursor:pointer" onclick="commentPost(${p.id})">💬 ${p.comments_count || 0}</span>
                </div>
              </div>` : `
              <a href="${p.article_link}" class="link-green" style="font-size: 13px; font-weight: 600;">Read Guide ↗</a>
              `}
            </div>
        </div>
    `;
}

function renderCampaigns(campaigns) {
  const container = document.getElementById('campaignsContainer');
  if (!container) return;

  if (!campaigns || campaigns.length === 0) {
    container.parentElement.style.display = 'none';
    return;
  }

  container.parentElement.style.display = 'block';

  const sorted = [...campaigns].sort((a, b) => {
    const engA = (a.engagement_count || 0) + (a.raised_amount || 0);
    const engB = (b.engagement_count || 0) + (b.raised_amount || 0);
    return engB - engA;
  });

  const c = sorted[0];

  if (c.target_amount) {
    const progress = (c.raised_amount / c.target_amount) * 100;
    container.innerHTML = `
        <div class="card" style="position:relative">
            ${isOwner ? `<button onclick="deleteCampaign(${c.id})" style="position:absolute; right:10px; top:10px; color:red;">🗑️</button>` : ''}
            <div class="card-content">
              <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <h4>${c.title}</h4>
                ${c.badge ? `<span class="badge green small">${c.badge}</span>` : ''}
              </div>
              <p>${c.description}</p>
              <div class="progress-section">
                <div class="progress-labels">
                  <strong>$${c.raised_amount.toLocaleString()} raised</strong>
                  <span class="text-green">${Math.round(progress)}%</span>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width: ${progress}%;"></div></div>
                <div class="progress-sub">GOAL: $${c.target_amount.toLocaleString()} • ${c.days_left} DAYS LEFT</div>
              </div>
              <button class="btn btn-dark w-full">Support Campaign</button>
            </div>
        </div>`;
  } else {
    container.innerHTML = `
        <div class="card feed-card" style="position:relative">
            ${isOwner ? `<button onclick="deleteCampaign(${c.id})" style="position:absolute; right:10px; top:10px; color:red; z-index:10;">🗑️</button>` : ''}
            <div class="feed-card-img" style="background-image: url('https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&q=80&w=400'); height: 120px;">
            </div>
            <div class="card-content">
              <h4>${c.title}</h4>
              <p>${c.description}</p>
              <div class="stewards-active">👥 ${c.engagement_count} Stewards Active</div>
            </div>
        </div>`;
  }
}

function renderStats(stats) {
  const container = document.getElementById('statsContainer');
  if (!container) return;

  container.style.display = 'block';

  const co2 = (stats && stats.co2_offset) ? stats.co2_offset : '0';
  const trees = (stats && stats.trees_planted) ? stats.trees_planted : '0';
  const water = (stats && stats.water_liters) ? stats.water_liters : '0';

  container.innerHTML = `
      <div class="card dark-card">
        <p class="stat-label">GLOBAL CO2 OFFSET</p>
        <div class="stat-main">${co2} <span>Tons</span></div>
        <div class="stat-grid">
          <div>
            <p class="stat-label">TREES PLANTED</p>
            <div class="stat-val">${trees}</div>
          </div>
          <div>
            <p class="stat-label">LITERS WATER</p>
            <div class="stat-val">${water}</div>
          </div>
        </div>
        <div class="map-bg"></div>
      </div>
    `;
}

function renderStewards(stewards) {
  const container = document.getElementById('stewardsContainer');
  if (!container) return;

  const card = container.closest('.card');
  if (!stewards || stewards.length === 0) {
    if (card) card.style.display = 'none';
    return;
  }
  if (card) card.style.display = 'block';

  container.innerHTML = stewards.map(s => `
        <div class="steward-item">
          <div class="s-info">
            <div class="avatar" style="background-image: url('https://i.pravatar.cc/100?img=${s.avatar_id}');"></div>
            <strong>${s.name}</strong>
          </div>
          <span class="text-green">${s.points.toLocaleString()} pts</span>
        </div>
    `).join('');
}

function addOwnerControls() {
  const feedHeader = document.querySelector('.feed-header > div');
  if (feedHeader && !document.getElementById('addPostBtn')) {
    feedHeader.innerHTML += `<button id="addPostBtn" class="btn btn-outline" style="padding: 4px 8px; font-size:11px; margin-top:8px;">+ Add Post</button>`;
    document.getElementById('addPostBtn').onclick = () => {
      const title = prompt("Post Title:");
      if (title) fetch('http://localhost:3000/api/posts', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ title, author_name: 'Admin Owner', type: 'ARTICLE' }) }).then(fetchData);
    };
  }
}

async function likePost(id) {
  await fetch(`http://localhost:3000/api/posts/${id}/like`, { method: 'POST' });
  fetchData();
}

async function commentPost(id) {
  await fetch(`http://localhost:3000/api/posts/${id}/comment`, { method: 'POST' });
  fetchData();
}

async function deletePost(id) {
  if (confirm('Delete post?')) {
    await fetch(`http://localhost:3000/api/posts/${id}`, { method: 'DELETE' });
    fetchData();
  }
}

async function deleteCampaign(id) {
  if (confirm('Delete campaign?')) {
    await fetch(`http://localhost:3000/api/campaigns/${id}`, { method: 'DELETE' });
    fetchData();
  }
}

// --- EXPLORE PAGE LOGIC ---

const explorePosts = [
  {
    id: 1,
    user_name: 'Sarah Jenkins',
    user_location: '2 hours ago • Portland, OR',
    title: 'Urban Park Cleanup',
    description: 'Removed 500lbs of trash and planted 20 native trees in the community park. Incredible teamwork today!',
    before_image: 'before.png',
    after_image: 'after.png',
    likes_count: 124,
    comments_count: 3,
    category: 'urban',
    liked: false,
    comments: [
      { user: 'Alex', text: 'Great job!' },
      { user: 'Sam', text: 'Thank you for doing this.' },
      { user: 'Jordan', text: 'Looks amazing now.' }
    ]
  },
  {
    id: 2,
    user_name: 'David Chen',
    user_location: '5 hours ago • Seattle, WA',
    title: 'River Bank Restoration',
    description: 'Cleared plastics from the shoreline to protect local marine life and restore the natural habitat.',
    before_image: 'before.png',
    after_image: 'after.png',
    likes_count: 89,
    comments_count: 1,
    category: 'river',
    liked: false,
    comments: [
      { user: 'Maya', text: 'We need more of this!' }
    ]
  }
];

function renderGoalMock() {
  const goalTitle = document.getElementById('goalTitle');
  if (!goalTitle) return;
  
  goalTitle.textContent = 'Plant 10,000 Trees this Month';
  document.getElementById('goalCurrent').textContent = '8,500 planted';
  document.getElementById('goalPercent').textContent = '85%';
  const prog = document.getElementById('goalProgress');
  if (prog) prog.style.width = '85%';
}

function renderExplorePosts(posts) {
  const container = document.getElementById('explorePostsContainer');
  if (!container) return;
  
  if (!posts || posts.length === 0) {
    container.innerHTML = '<p style="color: var(--text-muted); padding: 20px;">No impact stories found for this ecosystem.</p>';
    return;
  }

  container.innerHTML = posts.map(p => `
    <div class="card explore-card" style="margin-bottom: 24px;">
      <div class="split-image-container" style="position:relative; width:100%; height:300px; overflow:hidden; border-radius: 12px 12px 0 0;">
        <img src="${p.after_image}" class="img-left" alt="After" style="position:absolute; width:100%; height:100%; object-fit:cover;">
        <img src="${p.before_image}" class="img-right" id="exploreImgRight-${p.id}" alt="Before" style="position:absolute; width:100%; height:100%; object-fit:cover; clip-path:inset(0 0 0 50%);">
        
        <div class="split-label left" style="position:absolute; bottom:16px; left:16px; background:rgba(0,0,0,0.6); color:white; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;">AFTER</div>
        <div class="split-label right" style="position:absolute; bottom:16px; right:16px; background:rgba(0,0,0,0.6); color:white; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;">BEFORE</div>
        
        <div class="split-slider" id="exploreSplitSlider-${p.id}" style="position:absolute; top:0; bottom:0; left:50%; width:4px; background:white; transform:translateX(-50%); pointer-events:none;">
          <div class="slider-btn" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:32px; height:32px; background:white; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.3);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 13 12 18 17 13"></polyline><polyline points="7 6 12 11 17 6"></polyline></svg>
          </div>
        </div>
        <input type="range" min="0" max="100" value="50" class="explore-slider-input" data-id="${p.id}" style="position:absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:col-resize; z-index:10;">
      </div>

      <div class="card-content" style="padding: 24px;">
        <div class="user-meta" style="margin-bottom: 12px; display:flex; align-items:center; gap:12px;">
          <div class="avatar" style="background-image: url('https://i.pravatar.cc/100?u=${encodeURIComponent(p.user_name)}'); width:40px; height:40px; border-radius:50%; background-size:cover;"></div>
          <div>
            <strong style="display:block; font-size:14px;">${p.user_name}</strong>
            <span class="time" style="font-size:12px; color:var(--text-muted);">${p.user_location}</span>
          </div>
        </div>

        <h4 style="font-size:18px; margin:0 0 8px 0;">${p.title}</h4>
        <p style="font-size:14px; color:var(--text-muted); margin:0 0 16px 0;">${p.description}</p>

        <div class="feed-actions" style="display:flex; justify-content:space-between; align-items:center;">
          <div class="stats" style="display:flex; gap:16px;">
            <span class="like-btn" onclick="toggleLike(${p.id})" style="cursor:pointer; font-weight:600; display:flex; align-items:center; gap:6px; color: ${p.liked ? 'var(--text-green)' : 'inherit'}">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="${p.liked ? 'var(--text-green)' : 'none'}" stroke="currentColor" stroke-width="2" style="transition:all 0.2s;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              <span id="like-count-${p.id}">${p.likes_count}</span>
            </span>
            <span class="comment-btn" onclick="toggleCommentInput(${p.id})" style="cursor:pointer; font-weight:600; display:flex; align-items:center; gap:6px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
              <span id="comment-count-${p.id}">${p.comments_count}</span>
            </span>
          </div>
          <span class="badge small" style="background-color: var(--bg-badge); color: var(--text-green); padding:4px 8px; border-radius:12px; font-size:11px; font-weight:600;">#${p.category.toUpperCase()}</span>
        </div>

        <div class="comments-section" id="comments-section-${p.id}" style="display:none; margin-top:16px; border-top:1px solid #eee; padding-top:16px;">
          <div id="comments-list-${p.id}" style="margin-bottom:12px; max-height:150px; overflow-y:auto; display:flex; flex-direction:column; gap:8px;">
            ${p.comments.map(c => `
              <div style="font-size:13px; background:#f5f5f5; padding:8px 12px; border-radius:8px;">
                <strong>${c.user}:</strong> ${c.text}
              </div>
            `).join('')}
          </div>
          <div style="display:flex; gap:8px;">
            <input type="text" id="comment-input-${p.id}" placeholder="Write a comment..." style="flex:1; padding:8px 12px; border:1px solid #ddd; border-radius:20px; outline:none; font-size:13px;">
            <button onclick="addComment(${p.id})" class="btn btn-dark" style="padding:6px 16px; font-size:12px; border-radius:20px;">Post</button>
          </div>
        </div>
      </div>
    </div>
  `).join('');

  document.querySelectorAll('.explore-slider-input').forEach(input => {
    input.addEventListener('input', (e) => {
      const val = e.target.value;
      const id = e.target.dataset.id;
      document.getElementById(`exploreSplitSlider-${id}`).style.left = val + '%';
      document.getElementById(`exploreImgRight-${id}`).style.clipPath = `inset(0 0 0 ${val}%)`;
    });
  });
}

function initFilters() {
  const filterBtns = document.querySelectorAll('.filter-btn');
  if (filterBtns.length === 0) return;
  
  filterBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      filterBtns.forEach(b => b.classList.remove('active'));
      const targetBtn = e.currentTarget;
      targetBtn.classList.add('active');
      const category = targetBtn.dataset.category;
      
      const filtered = category === 'all' ? explorePosts : explorePosts.filter(p => p.category === category);
      renderExplorePosts(filtered);
    });
  });
}

window.toggleLike = function(id) {
  const post = explorePosts.find(p => p.id === id);
  if (post) {
    post.liked = !post.liked;
    post.likes_count += post.liked ? 1 : -1;
    
    const countEl = document.getElementById(`like-count-${id}`);
    if (countEl) countEl.textContent = post.likes_count;
    
    const btn = countEl.closest('.like-btn');
    if (btn) {
      btn.style.color = post.liked ? 'var(--text-green)' : 'inherit';
      const svg = btn.querySelector('svg');
      if (svg) {
        svg.setAttribute('fill', post.liked ? 'var(--text-green)' : 'none');
      }
    }
  }
}

window.toggleCommentInput = function(id) {
  const section = document.getElementById(`comments-section-${id}`);
  if (section) {
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
  }
}

window.addComment = function(id) {
  const post = explorePosts.find(p => p.id === id);
  const input = document.getElementById(`comment-input-${id}`);
  if (post && input && input.value.trim() !== '') {
    const text = input.value.trim();
    const user = currentUser ? currentUser.name : 'Anonymous User';
    post.comments.push({ user, text });
    post.comments_count++;
    
    const countEl = document.getElementById(`comment-count-${id}`);
    if (countEl) countEl.textContent = post.comments_count;
    
    const listEl = document.getElementById(`comments-list-${id}`);
    if (listEl) {
      const commentHtml = `
        <div style="font-size:13px; background:#f5f5f5; padding:8px 12px; border-radius:8px;">
          <strong>${user}:</strong> ${text}
        </div>
      `;
      listEl.insertAdjacentHTML('beforeend', commentHtml);
      listEl.scrollTop = listEl.scrollHeight;
    }
    
    input.value = '';
  }
}
