const App = {
    currentPage: 'accueil',
    loading: false,
    errors: [],
    registrationsChartInstance: null,

    init() {
        this.setupEventListeners();
        this.loadInitialPage();
    },

    showLoader() {
        this.loading = true;
        const loader = document.getElementById('global-loader');
        const loaderBar = document.getElementById('loader-bar');
        if(loader) loader.style.display = 'block';
        if(loaderBar) loaderBar.style.width = '70%';
    },

    hideLoader() {
        const loader = document.getElementById('global-loader');
        const loaderBar = document.getElementById('loader-bar');
        if(loaderBar) loaderBar.style.width = '100%';

        setTimeout(() => {
            if(loader) loader.style.opacity = '0';
            setTimeout(() => {
                if(loader) {
                    loader.style.display = 'none';
                    loader.style.opacity = '1';
                }
                if(loaderBar) loaderBar.style.width = '0';
                this.loading = false;
            }, 300);
        }, 300);
    },

    showError(title, message, retryCallback = null, type = 'error') {
        const errorId = 'error-' + Date.now();
        const errorContainer = document.getElementById('error-container');
        if (!errorContainer) return;

        const errorCard = document.createElement('div');
        errorCard.className = `error-card ${type}`;
        errorCard.id = errorId;
        errorCard.innerHTML = `
            <div class="error-title">${title}</div>
            <div class="error-message">${message}</div>
            <div class="error-actions">
                ${retryCallback ? `<button class="retry-btn">Réessayer</button>` : ''}
                <button class="dismiss-btn">Fermer</button>
            </div>
        `;

        errorContainer.appendChild(errorCard);
        errorContainer.style.display = 'block';

        if (retryCallback) {
            errorCard.querySelector('.retry-btn').addEventListener('click', () => {
                retryCallback();
                errorCard.remove();
                if (!errorContainer.children.length) errorContainer.style.display = 'none';
            });
        }

        errorCard.querySelector('.dismiss-btn').addEventListener('click', () => {
            errorCard.remove();
            if (!errorContainer.children.length) errorContainer.style.display = 'none';
        });

        setTimeout(() => {
            if (document.getElementById(errorId)) {
                errorCard.remove();
                if (!errorContainer.children.length) errorContainer.style.display = 'none';
            }
        }, 10000);
    },

    updateActiveMenu() {
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.toggle('active', item.dataset.page === this.currentPage);
            item.setAttribute('aria-current', item.dataset.page === this.currentPage ? 'page' : 'false');
        });
    },

    async loadPage(page) {
        if (this.loading) return;

        this.currentPage = page;
        this.showLoader();
        this.updateActiveMenu();

        try {
            const response = await fetch(`?action=load_page&page=${encodeURIComponent(page)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                throw new Error(`Réponse inattendue: ${textResponse.substring(0, 100)}`);
            }

            const data = await response.json();
            if (!response.ok || data.error) {
                throw new Error(data.error || 'Erreur de chargement');
            }

            if (data.success) {
                document.getElementById('content-container').innerHTML = data.content;
                document.getElementById('page-title').textContent = data.pageTitle;
                history.pushState({ page }, '', `?page=${page}`);

                if (page === 'accueil') {
                    this.initChart();
                }

                // Re-initialize event listeners for dynamic content (ex: boutons vérifier, dropdowns)
                this.initDynamicContentEvents();
            }
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
            this.showError('Erreur de chargement', error.message, () => this.loadPage(page));
        } finally {
            this.hideLoader();
        }
    },

    loadInitialPage() {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page') || 'accueil';

        if (document.getElementById('content-container').innerHTML.trim() !== '') {
            this.currentPage = page;
            this.updateActiveMenu();
            document.getElementById('page-title').textContent = page.charAt(0).toUpperCase() + page.slice(1);
            if (page === 'accueil') this.initChart();
            this.initDynamicContentEvents();
        } else {
            this.loadPage(page);
        }
    },

    initChart() {
        const colors = {
            primary: '#2F855A',
            secondary: '#ED8936',
            success: '#38A169',
            danger: '#E53E3E',
            dark: '#2D3748',
            medium: '#718096',
            light: '#E2E8F0',
            background: '#F0FFF4'
        };

        const ctx = document.getElementById('registrationsChart')?.getContext('2d');
        if (!ctx) return;

        if (this.registrationsChartInstance) {
            this.registrationsChartInstance.destroy();
            this.registrationsChartInstance = null;
        }

        // Remplace les lignes suivantes par la récupération réelle des données côté PHP
        const chartData = {
            labels: <?= json_encode(array_map(function($item) {
                return DateTime::createFromFormat('Y-m', $item['month'])->format('M Y');
            }, $stats)) ?>,
            datasets: [
                {
                    label: 'Agriculteurs',
                    data: <?= json_encode(array_column($stats, 'agriculteurs')) ?>,
                    backgroundColor: this.hexToRgba(colors.primary, 0.7),
                    borderColor: colors.primary,
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.6
                },
                {
                    label: 'Marchés',
                    data: <?= json_encode(array_column($stats, 'marches')) ?>,
                    backgroundColor: this.hexToRgba(colors.secondary, 0.7),
                    borderColor: colors.secondary,
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.6
                }
            ]
        };

        const config = {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { color: colors.medium }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: colors.medium }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: colors.dark,
                            font: { family: 'Poppins' },
                            boxWidth: 12,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: this.hexToRgba(colors.dark, 0.95),
                        titleFont: {
                            family: 'Poppins',
                            size: 12,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Inter',
                            size: 11
                        },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: true,
                        boxWidth: 8,
                        boxHeight: 8
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        };

        this.registrationsChartInstance = new Chart(ctx, config);

        // Gestion du filtre temporel
        document.querySelectorAll('.time-filter button').forEach(button => {
            button.addEventListener('click', function () {
                document.querySelector('.time-filter button.active')?.classList.remove('active');
                this.classList.add('active');
                // Ici, possibilité de recharger les données du graphique
            });
        });
    },

    hexToRgba(hex, alpha = 1) {
        const [r, g, b] = hex.match(/\w\w/g).map(x => parseInt(x, 16));
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    },

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    getDocumentTypeName(type) {
        return {
            'piece_identite': 'Pièce d\'identité',
            'certificat_culture': 'Certificat de culture',
            'photo_champ': 'Photo du champ (facultative)'
        }[type] || type;
    },

    verifyAgriculteurAccount(id) {
        if (!confirm("Confirmez-vous la validation de ce compte agriculteur ?")) return;

        this.showLoader();
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=verifier&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.loadPage('agriculteurs');
                this.showError('Succès', 'Compte validé avec succès', null, 'success');
            } else {
                throw new Error(data.error || 'Erreur inconnue');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showError('Erreur', error.message);
        })
        .finally(() => this.hideLoader());
    },

    initDynamicContentEvents() {
        // Dropdown details toggle
        document.querySelectorAll('.btn-view-dropdown').forEach(btn => {
            btn.onclick = () => {
                const id = btn.dataset.id;
                const dropdown = document.getElementById(`dropdown-${id}`);
                if (!dropdown) return;

                const dropdownContent = dropdown.querySelector('.dropdown-details');
                const isOpen = dropdown.style.display === 'table-row';

                // Close all dropdowns first
                document.querySelectorAll('.dropdown-content').forEach(d => d.style.display = 'none');

                if (!isOpen) {
                    dropdown.style.display = 'table-row';

                    if (dropdownContent.innerHTML.includes('Chargement')) {
                        fetch('', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `action=get_details&id=${id}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) throw new Error(data.error);

                            const documentsHTML = data.documents.map(doc => `
                                <div class="document-item">
                                    <i class="fas fa-file-${doc.type_doc === 'piece_identite' ? 'id-card' : 
                                                       doc.type_doc === 'certificat_culture' ? 'certificate' : 'image'}"></i>
                                    <div class="document-info">
                                        <span class="document-name">${this.getDocumentTypeName(doc.type_doc)}</span>
                                        <small>${this.formatDate(doc.date_televersement)}</small>
                                        ${doc.statut !== 'approuve' ? `<span class="document-status pending">En attente</span>` : ''}
                                    </div>
                                    <a href="users/agriculteur/uploads/${doc.chemin}" class="btn-download" download target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            `).join('');

                            dropdownContent.innerHTML = `
                                <div class="user-details-content">
                                    <div class="detail-group">
                                        <h4>Informations personnelles</h4>
                                        <div class="detail-row"><label>ID:</label><span>${data.agriculteur.id}</span></div>
                                        <div class="detail-row"><label>Nom complet:</label><span>${data.agriculteur.nom_complet}</span></div>
                                        <div class="detail-row"><label>Email:</label><span>${data.agriculteur.email}</span></div>
                                        <div class="detail-row"><label>Téléphone:</label><span>${data.agriculteur.telephone || 'N/A'}</span></div>
                                        <div class="detail-row"><label>Date d'inscription:</label><span>${this.formatDate(data.agriculteur.date_inscription)}</span></div>
                                        <div class="detail-row">
                                            <label>Statut:</label>
                                            <span class="status-badge ${data.agriculteur.compte_verifie ? 'verified' : 'pending'}">
                                                <i class="fas fa-${data.agriculteur.compte_verifie ? 'check-circle' : 'clock'}"></i>
                                                ${data.agriculteur.compte_verifie ? 'Vérifié' : 'Non vérifié'}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="detail-group">
                                        <h4>Documents soumis</h4>
                                        ${!data.hasRequiredDocs ? `
                                            <div class="documents-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <p>Documents obligatoires manquants</p>
                                            </div>` : ''}
                                        ${data.documents.length > 0 ? `<div class="documents-list">${documentsHTML}</div>` : '<p class="no-documents">Aucun document disponible</p>'}
                                        ${data.agriculteur.date_demande_verification && !data.agriculteur.compte_verifie ? `
                                            <div class="verification-actions">
                                                ${data.hasRequiredDocs ? `
                                                    <button class="btn-verify-dropdown" data-id="${data.agriculteur.id}">
                                                        <i class="fas fa-check"></i> Valider ce compte
                                                    </button>` : `
                                                    <p class="verification-hint">
                                                        <i class="fas fa-info-circle"></i> Les documents obligatoires doivent être fournis avant validation
                                                    </p>`
                                                }
                                            </div>` : ''}
                                    </div>
                                </div>
                            `;

                            // Attacher l'écouteur sur bouton valider compte
                            const verifyBtn = dropdownContent.querySelector('.btn-verify-dropdown');
                            if (verifyBtn) {
                                verifyBtn.onclick = () => this.verifyAgriculteurAccount(id);
                            }
                        })
                        .catch(error => {
                            dropdownContent.innerHTML = `
                                <div class="error-message">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <p>${error.message}</p>
                                    <button class="btn-retry" onclick="location.reload()">
                                        <i class="fas fa-sync-alt"></i> Réessayer
                                    </button>
                                </div>
                            `;
                        });
                    }
                } else {
                    dropdown.style.display = 'none';
                }
            };
        });

        // Boutons vérifier compte
        document.querySelectorAll('.btn-verify').forEach(btn => {
            btn.onclick = () => this.verifyAgriculteurAccount(btn.dataset.id);
        });
    },

    setupEventListeners() {
        document.querySelectorAll('.menu-item[data-page]').forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();
                if (item.dataset.page !== this.currentPage) {
                    this.loadPage(item.dataset.page);
                }
            });
        });

        window.addEventListener('popstate', e => {
            const page = e.state?.page || 'accueil';
            if (page !== this.currentPage) {
                this.loadPage(page);
            }
        });

        document.addEventListener('click', e => {
            const link = e.target.closest('a[data-page]');
            if (link) {
                e.preventDefault();
                this.loadPage(link.dataset.page);
            }
        });

        window.addEventListener('unhandledrejection', event => {
            console.error('Promesse rejetée non capturée:', event.reason);
        });
    }
};
