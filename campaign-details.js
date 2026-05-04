document.addEventListener('DOMContentLoaded', () => {
    // Get campaign ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    let campaignId = urlParams.get('id');

    // Default to 1 if no ID provided for demonstration purposes
    if (!campaignId) {
        campaignId = 1;
    }

    let currentCampaign = null;

    // Fetch campaign data
    fetch(`http://localhost:3000/api/campaigns/${campaignId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(campaign => {
            currentCampaign = campaign;
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('content-state').style.display = 'grid'; // because we use grid in CSS

            // Render Data
            document.getElementById('camp-title').textContent = campaign.title || 'Untitled Campaign';
            document.getElementById('camp-location').textContent = campaign.location || 'Unknown Location';
            
            const goal = campaign.goal || campaign.target_amount || 0;
            document.getElementById('camp-goal-text').textContent = goal.toLocaleString();
            
            // For progress, fallback to calculated if not explicitly stored
            let progress = campaign.progress_percent;
            if (progress === undefined || progress === null) {
                if (goal > 0) {
                    progress = Math.round(((campaign.raised_amount || 0) / goal) * 100);
                } else {
                    progress = 0;
                }
            }
            
            document.getElementById('camp-progress-text').textContent = `${progress}%`;
            document.getElementById('camp-progress-bar').style.width = `${progress}%`;
            
            document.getElementById('camp-trees').textContent = (campaign.trees_planted || 0).toLocaleString();
            document.getElementById('camp-volunteers').textContent = (campaign.volunteers_count || 0).toLocaleString();
            
            if (campaign.upcoming_event) {
                document.getElementById('camp-event').textContent = campaign.upcoming_event;
            } else {
                document.getElementById('camp-event-container').style.display = 'none';
            }

            // Initialize Map
            const lat = campaign.latitude || 0;
            const lng = campaign.longitude || 0;
            
            const map = L.map('map').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map)
                .bindPopup(`<b>${campaign.title}</b><br>${campaign.location}`)
                .openPopup();
        })
        .catch(err => {
            console.error('Error fetching campaign:', err);
            document.getElementById('loading-state').style.display = 'none';
            document.getElementById('error-state').style.display = 'block';
        });

    // Handle Join Button
    const joinBtn = document.getElementById('join-btn');
    if (joinBtn) {
        joinBtn.addEventListener('click', () => {
            joinBtn.disabled = true;
            joinBtn.textContent = 'Joining...';

            // Get logged in user or default to user 1
            let userId = 1;
            try {
                const userStr = localStorage.getItem('user');
                if (userStr && userStr !== 'undefined') {
                    const userObj = JSON.parse(userStr);
                    if (userObj && userObj.id) {
                        userId = userObj.id;
                    }
                }
            } catch (e) {
                console.error("Error reading user from localStorage");
            }

            fetch(`http://localhost:3000/api/campaigns/${campaignId}/join`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.message === 'Joined successfully') {
                    joinBtn.textContent = 'Joined';
                    // Increment volunteers count in UI
                    const volEl = document.getElementById('camp-volunteers');
                    const currentVols = parseInt(volEl.textContent.replace(/,/g, '')) || 0;
                    volEl.textContent = (currentVols + 1).toLocaleString();
                } else if (data.message === 'Already joined' || (data.error && data.error.includes('UNIQUE constraint failed'))) {
                    joinBtn.textContent = 'Already Joined';
                } else {
                    joinBtn.disabled = false;
                    joinBtn.textContent = 'Join this Campaign';
                    alert('Error joining campaign: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Join error:', err);
                joinBtn.disabled = false;
                joinBtn.textContent = 'Join this Campaign';
                alert('Failed to join the campaign. Please try again later.');
            });
        });
    }
});
