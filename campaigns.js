document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('campaignsGrid');
    const filterBtns = document.querySelectorAll('.cat-btn');
    const subscribeBtn = document.getElementById('subscribeBtn');
    
    // Fetch and render campaigns
    async function loadCampaigns(category = 'all') {
        try {
            const res = await fetch(`http://localhost:3000/api/campaigns?category=${encodeURIComponent(category)}`);
            const data = await res.json();
            renderCampaigns(data);
        } catch (err) {
            console.error('Error fetching campaigns:', err);
            grid.innerHTML = '<p>Error loading campaigns.</p>';
        }
    }

    function renderCampaigns(campaigns) {
        grid.innerHTML = '';
        if (campaigns.length === 0) {
            grid.innerHTML = '<p style="text-align:center; color:#666; grid-column: 1/-1;">No campaigns found for this category.</p>';
            return;
        }

        campaigns.forEach(camp => {
            const card = document.createElement('div');
            card.className = 'campaign-card';
            
            // Determine progress or capacity label based on status
            const progressLabel = camp.status === 'ACTIVE' ? 'Progress' : 'Capacity';
            
            card.innerHTML = `
                <div class="card-image-wrapper">
                    <img src="${camp.image_url}" alt="${camp.title}" class="card-image">
                    <div class="card-badge ${camp.status}">${camp.status}</div>
                </div>
                <div class="card-content">
                    <div class="card-date">${camp.date}</div>
                    <h3 class="card-title">${camp.title}</h3>
                    
                    <div class="card-meta">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span>${camp.location}</span>
                    </div>
                    
                    <div class="card-meta">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <span>${camp.stewards_count} ${camp.status === 'ACTIVE' ? 'Stewardship Partners' : 'Pre-registered'}</span>
                    </div>
                    
                    <div class="card-bottom">
                        <div class="progress-container">
                            <div class="progress-text">
                                <span>${progressLabel}</span>
                                <span>${camp.progress_percent}%</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: ${camp.progress_percent}%;"></div>
                            </div>
                        </div>
                        <button class="join-btn" onclick="alert('You joined this campaign!')">Join</button>
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    // Filter Buttons logic
    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            
            // Load filtered data
            const cat = e.target.getAttribute('data-cat');
            loadCampaigns(cat);
        });
    });

    // Newsletter Subscribe logic
    if (subscribeBtn) {
        subscribeBtn.addEventListener('click', () => {
            alert('Subscribed!');
            document.getElementById('newsletterEmail').value = '';
        });
    }

    // Initial load
    loadCampaigns();
});
