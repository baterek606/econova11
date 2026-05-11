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

  // Fetching Data Logic for Home Page
  const postsContainer = document.getElementById('postsContainer');
  if (postsContainer) {
    fetchData();
  }


});

async function fetchData() {
  try {
    const fetchJson = async (url) => {
      try {
        const res = await fetch(url);
        if (!res.ok) return null;
        return await res.json();
      } catch (e) {
        console.warn(`Failed to fetch ${url}`, e);
        return null;
      }
    };

    const [posts, campaigns, stats, stewards] = await Promise.all([
      fetchJson('api_get_posts.php'),
      fetchJson('api_get_campaigns.php'),
      fetchJson('http://localhost:3000/api/stats'),
      fetchJson('http://localhost:3000/api/stewards')
    ]);

    if (posts) renderPosts(posts);
    if (campaigns) renderCampaigns(campaigns);
    if (stats) renderStats(stats);
    if (stewards) renderStewards(stewards);

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

  const sortedPosts = [...posts].sort((a, b) => (b.likes_count || 0) - (a.likes_count || 0));
  const topPosts = sortedPosts.slice(0, 2);

  container.innerHTML = topPosts.map(p => `
        <div class="card feed-card" style="position:relative; margin-bottom: 20px;">
            ${typeof isOwner !== 'undefined' && isOwner ? `<button onclick="deletePost(${p.id})" style="position:absolute; right:10px; top:10px; color:red; z-index:10;">🗑️</button>` : ''}
            ${p.type === 'REFORESTATION' ? `
            <div class="feed-card-img" style="background-image: url('${(p.after_image && p.after_image !== 'local') ? p.after_image : ((p.before_image && p.before_image !== 'local') ? p.before_image : 'https://images.unsplash.com/photo-1591193116044-f9dd3592e56d?auto=format&fit=crop&q=80&w=400')}'); background-size: cover; background-position: center;">
              <span class="badge white">${p.type}</span>
            </div>` : ''}
            <div class="card-content">
              <div class="user-meta">
                ${p.type === 'REFORESTATION' ?
      `<div class="avatar" style="background-image: url('https://i.pravatar.cc/100?u=${encodeURIComponent(p.author_name)}');"></div>` :
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
                  <span id="like-btn-${p.id}" style="cursor:pointer; color: ${p.user_liked ? 'var(--text-green)' : 'inherit'};" onclick="if(document.querySelector('.btn-join')){ alert('Please login first'); window.location.href='login.php'; } else { toggleLike(${p.id}); }">${p.user_liked ? '❤️' : '♡'} <span id="like-count-${p.id}" data-raw="${p.likes_count || 0}">${p.likes_count >= 1000 ? (p.likes_count/1000).toFixed(1)+'k' : (p.likes_count || 0)}</span></span>
                  <span style="cursor:pointer" onclick="if(document.querySelector('.btn-join')){ alert('Please login first'); window.location.href='login.php'; } else { toggleCommentSection(${p.id}); }">💬 <span id="comment-count-${p.id}" data-raw="${p.comments_count || 0}">${p.comments_count >= 1000 ? (p.comments_count/1000).toFixed(1)+'k' : (p.comments_count || 0)}</span></span>
                </div>
              </div>
              <div id="comments-section-${p.id}" style="display:none; margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                <div id="comments-list-${p.id}" style="max-height: 150px; overflow-y: auto; margin-bottom: 10px; font-size: 13px;"></div>
                <div style="display:flex; gap:8px;">
                    <textarea id="comment-input-${p.id}" placeholder="Write a comment..." style="flex:1; padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; resize:vertical; min-height:36px;"></textarea>
                    <button onclick="submitComment(${p.id})" class="btn btn-dark" style="padding: 6px 12px; font-size: 13px; height:fit-content;">Post</button>
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

  const topCampaigns = sorted.slice(0, 2);

  container.innerHTML = topCampaigns.map(c => {
    if (c.target_amount) {
      const progress = (c.raised_amount / c.target_amount) * 100;
      return `
        <div class="card" style="position:relative; margin-bottom: 20px;">
            ${typeof isOwner !== 'undefined' && isOwner ? `<button onclick="deleteCampaign(${c.id})" style="position:absolute; right:10px; top:10px; color:red;">🗑️</button>` : ''}
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
              <button class="btn btn-dark w-full" onclick="if(document.querySelector('.btn-join')){ alert('Please login to join'); window.location.href='login.php'; } else { joinCampaign(${c.id}); }">Support Campaign</button>
            </div>
        </div>`;
    } else {
      return `
        <div class="card feed-card" style="position:relative; margin-bottom: 20px;">
            ${typeof isOwner !== 'undefined' && isOwner ? `<button onclick="deleteCampaign(${c.id})" style="position:absolute; right:10px; top:10px; color:red; z-index:10;">🗑️</button>` : ''}
            <div class="feed-card-img" style="background-image: url('${c.image_url ? c.image_url : 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&q=80&w=400'}'); height: 120px; background-size: cover; background-position: center;">
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

window.toggleLike = async function(id) {
  try {
    const response = await fetch(`api_toggle_like.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: id })
    });
    const data = await response.json();
    if (data.success) {
      const countSpan = document.getElementById(`like-count-${id}`);
      if (!countSpan) return;
      const btnSpan = document.getElementById(`like-btn-${id}`);
      
      const formattedCount = data.likes_count >= 1000 ? (data.likes_count/1000).toFixed(1) + 'k' : data.likes_count;
      countSpan.setAttribute('data-raw', data.likes_count);
      
      if (data.liked) {
        btnSpan.innerHTML = `❤️ <span id="like-count-${id}" data-raw="${data.likes_count}">${formattedCount}</span>`;
      } else {
        btnSpan.innerHTML = `♡ <span id="like-count-${id}" data-raw="${data.likes_count}">${formattedCount}</span>`;
      }
    } else {
      if (data.error === 'Please login first') {
        alert(data.error);
        window.location.href = 'login.php';
      } else {
        console.error(data.error);
      }
    }
  } catch (e) {
    console.error('Error toggling like', e);
  }
};

window.toggleCommentSection = async function(id) {
  const section = document.getElementById(`comments-section-${id}`);
  if (!section) return;
  if (section.style.display === 'none') {
    section.style.display = 'block';
    await window.loadComments(id);
  } else {
    section.style.display = 'none';
  }
};

window.loadComments = async function(id) {
  try {
    const response = await fetch(`api_get_comments.php?post_id=${id}`);
    const data = await response.json();
    if (data.success) {
      const list = document.getElementById(`comments-list-${id}`);
      if (list) {
        list.innerHTML = data.comments.map(c => `
          <div style="margin-bottom: 8px;">
            <strong>${c.user_name}</strong> <span style="color:#888; font-size:11px;">${c.created_at}</span>
            <div style="margin-top: 2px;">${c.comment_text}</div>
          </div>
        `).join('');
      }
      const countSpan = document.getElementById(`comment-count-${id}`);
      if (countSpan) {
        countSpan.setAttribute('data-raw', data.comments.length);
        countSpan.textContent = data.comments.length >= 1000 ? (data.comments.length/1000).toFixed(1) + 'k' : data.comments.length;
      }
    }
  } catch (e) {
    console.error('Error loading comments', e);
  }
};

window.submitComment = async function(id) {
  const input = document.getElementById(`comment-input-${id}`);
  const text = input ? input.value.trim() : '';
  if (!text) return;

  try {
    const response = await fetch(`api_add_comment.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: id, comment_text: text })
    });
    const data = await response.json();
    if (data.success) {
      if (input) input.value = '';
      const countSpan = document.getElementById(`comment-count-${id}`);
      if (countSpan) {
        countSpan.setAttribute('data-raw', data.comments_count);
        countSpan.textContent = data.comments_count >= 1000 ? (data.comments_count/1000).toFixed(1) + 'k' : data.comments_count;
      }
      await window.loadComments(id);
    } else {
      if (data.error === 'Please login first') {
        alert(data.error);
        window.location.href = 'login.php';
      } else {
        console.error(data.error);
      }
    }
  } catch (e) {
    console.error('Error submitting comment', e);
  }
};

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

// Rewards Modal Logic
window.openRewardsModal = function() {
  let modal = document.getElementById('rewardsModal');
  if (!modal) {
      modal = document.createElement('div');
      modal.id = 'rewardsModal';
      modal.className = 'modal-overlay';
      modal.innerHTML = `
          <div class="modal-content" style="max-width: 400px; padding: 30px; border-radius: 12px; background: white; position: relative; text-align: center;">
              <button onclick="closeRewardsModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #666; line-height: 1;">&times;</button>
              <div style="font-size: 48px; margin-bottom: 15px;">🎁</div>
              <h3 style="margin-bottom: 15px; color: var(--text-main);">Rewards & Exchange</h3>
              <p style="color: var(--text-muted); line-height: 1.5; font-size: 14px;">Rewards system coming soon. You need at least 500 points to redeem coupons for eco-friendly products.</p>
              <button onclick="closeRewardsModal()" class="btn btn-dark" style="margin-top: 24px; width: 100%; padding: 10px; border-radius: 20px;">Understood</button>
          </div>
      `;
      // User must explicitly close the modal via button
      document.body.appendChild(modal);
  }
  modal.style.display = 'flex';
};

window.closeRewardsModal = function() {
  const modal = document.getElementById('rewardsModal');
  if (modal) modal.style.display = 'none';
};

// Campaign Join Logic
window.joinCampaign = async function(id) {
  try {
    const response = await fetch('api_join_campaign.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ campaign_id: id })
    });
    const data = await response.json();
    if (data.success) {
      if (data.new_join) {
        alert("Success! You've joined the campaign! Refreshing your score...");
        window.location.reload(); // Reload to update score and campaign stats
      } else {
        alert(data.message); // Already joined
      }
    } else {
      if (data.error === 'Please login first') {
        alert(data.error);
        window.location.href = 'login.php';
      } else {
        alert("Error: " + data.error);
      }
    }
  } catch (e) {
    console.error('Error joining campaign', e);
    alert('An error occurred. Please try again.');
  }
};
