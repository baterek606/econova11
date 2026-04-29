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
    signupForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const name = document.getElementById('fullName').value;
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      
      try {
          const res = await fetch('http://localhost:3000/api/auth/signup', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ name, email, password })
          });
          const data = await res.json();
          if (data.error) return alert(data.error);
          
          localStorage.setItem('user', JSON.stringify(data.user));
          alert('Account created successfully!');
          window.location.href = 'login.html';
      } catch (err) {
          alert('Error signing up');
      }
    });
  }

  // Fetching Data Logic
  const postsContainer = document.getElementById('postsContainer');
  if (postsContainer) {
    fetchData();
  }
});

const currentUser = JSON.parse(localStorage.getItem('user'));
const isOwner = currentUser && currentUser.role === 'owner';

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
        
        if (isOwner) addOwnerControls();
    } catch (e) {
        console.error('Error fetching data', e);
    }
}

function renderPosts(posts) {
    const container = document.getElementById('postsContainer');
    if (!container) return;
    
    container.innerHTML = posts.map(p => `
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
                  <span style="cursor:pointer" onclick="likePost(${p.id})">♡ ${p.likes_count}</span>
                  <span style="cursor:pointer" onclick="commentPost(${p.id})">💬 ${p.comments_count}</span>
                </div>
              </div>` : `
              <a href="${p.article_link}" class="link-green" style="font-size: 13px; font-weight: 600;">Read Guide ↗</a>
              `}
            </div>
        </div>
    `).join('');
}

function renderCampaigns(campaigns) {
    const container = document.getElementById('campaignsContainer');
    if (!container) return;
    
    container.innerHTML = campaigns.map(c => {
        if (c.target_amount) {
            const progress = (c.raised_amount / c.target_amount) * 100;
            return `
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
            return `
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
    }).join('');
}

function renderStats(stats) {
    const container = document.getElementById('statsContainer');
    if (!container || !stats) return;
    
    container.innerHTML = `
      <div class="card dark-card">
        <p class="stat-label">GLOBAL CO2 OFFSET</p>
        <div class="stat-main">${stats.co2_offset} <span>Tons</span></div>
        <div class="stat-grid">
          <div>
            <p class="stat-label">TREES PLANTED</p>
            <div class="stat-val">${stats.trees_planted}</div>
          </div>
          <div>
            <p class="stat-label">LITERS WATER</p>
            <div class="stat-val">${stats.water_liters}</div>
          </div>
        </div>
        <div class="map-bg"></div>
      </div>
    `;
}

function renderStewards(stewards) {
    const container = document.getElementById('stewardsContainer');
    if (!container) return;
    
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
            if (title) fetch('http://localhost:3000/api/posts', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({title, author_name:'Admin Owner', type:'ARTICLE'})}).then(fetchData);
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
    if(confirm('Delete post?')) {
        await fetch(`http://localhost:3000/api/posts/${id}`, { method: 'DELETE' });
        fetchData();
    }
}

async function deleteCampaign(id) {
    if(confirm('Delete campaign?')) {
        await fetch(`http://localhost:3000/api/campaigns/${id}`, { method: 'DELETE' });
        fetchData();
    }
}
