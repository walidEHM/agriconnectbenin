<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Téléversement de documents - AgriConnect BENIN</title>
    <link href="https://unpkg.com/lucide@latest/dist/lucide.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/css/autoComplete.min.css">
    <link rel="stylesheet" href="assets/css/style_uploads.css">
</head>

<body>
    <div class="container_televersement">
        <div class="logo" style="text-align: center;">
            <img src="../../assets/images/logo/Logo_back-off.png" alt="Logo AgriConnect BENIN" height="65px">
        </div>
        <h1><i class="icon" data-lucide="upload"></i> Téléversement des documents</h1>

        <!-- Notification importante pour l'agriculteur -->
        <div class="notification">
            <i class="icon notification-icon" data-lucide="alert-circle"></i>
            <div>
                <strong>Information importante :</strong> Pour finaliser votre inscription sur AgriConnect, vous devez
                fournir les documents demandés. Ces documents nous permettent de valider votre statut d'agriculteur et
                de vous proposer les meilleurs services adaptés à votre activité.
            </div>
        </div>

        <div class="progress-steps">
            <div class="progress-bar" style="width: 0%;"></div>
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Documents</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Contact</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Localisation</div>
            </div>
        </div>

        <form id="documentUploadForm">
            <!-- Étape 1 : Téléversement des documents -->
            <div class="form-step active" id="step1">
                <div class="form-group" id="identityDocGroup">
                    <label class="required-field">Pièce d'identité</label>
                    <div class="file-upload" id="identityUpload">
                        <div class="file-upload-icon">
                            <i class="icon" data-lucide="id-card"></i>
                        </div>
                        <div class="file-upload-text">Cliquez pour téléverser votre pièce d'identité</div>
                        <div class="file-upload-hint">Formats acceptés : JPG, PNG, PDF (max 2 Mo)</div>
                        <input type="file" id="identityFile" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                    <div class="error-message" id="identityError"></div>
                    <div class="preview-container" id="identityPreview">
                        <div class="preview-title">Pièce d'identité téléversée :</div>
                        <div class="document-preview">
                            <div class="document-icon">
                                <i class="icon" data-lucide="file-text"></i>
                            </div>
                            <div class="document-info">
                                <div class="document-name" id="identityFileName"></div>
                                <div class="document-size" id="identityFileSize"></div>
                            </div>
                            <div class="document-actions">
                                <button type="button" class="btn btn-outline" id="identityChangeBtn">
                                    <i class="icon" data-lucide="edit"></i> Modifier
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="certificateDocGroup" style="display: none;">
                    <label class="required-field">Certificat de culture</label>
                    <div class="file-upload" id="certificateUpload">
                        <div class="file-upload-icon">
                            <i class="icon" data-lucide="file-check"></i>
                        </div>
                        <div class="file-upload-text">Cliquez pour téléverser votre certificat</div>
                        <div class="file-upload-hint">Formats acceptés : JPG, PNG, PDF (max 2 Mo)</div>
                        <input type="file" id="certificateFile" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                    <div class="error-message" id="certificateError"></div>
                    <div class="preview-container" id="certificatePreview">
                        <div class="preview-title">Certificat téléversé :</div>
                        <div class="document-preview">
                            <div class="document-icon">
                                <i class="icon" data-lucide="file-text"></i>
                            </div>
                            <div class="document-info">
                                <div class="document-name" id="certificateFileName"></div>
                                <div class="document-size" id="certificateFileSize"></div>
                            </div>
                            <div class="document-actions">
                                <button type="button" class="btn btn-outline" id="certificateChangeBtn">
                                    <i class="icon" data-lucide="edit"></i> Modifier
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="fieldPhotoGroup" style="display: none;">
                    <label>Photo du champ (facultative)</label>
                    <div class="file-upload" id="fieldPhotoUpload">
                        <div class="file-upload-icon">
                            <i class="icon" data-lucide="image"></i>
                        </div>
                        <div class="file-upload-text">Cliquez pour téléverser la photo de votre champ</div>
                        <div class="file-upload-hint">Formats acceptés : JPG, PNG (max 3 Mo)</div>
                        <input type="file" id="fieldPhotoFile" accept=".jpg,.jpeg,.png">
                    </div>
                    <div class="error-message" id="fieldPhotoError"></div>
                    <div class="preview-container" id="fieldPhotoPreview">
                        <div class="preview-title">Photo du champ :</div>
                        <div class="document-preview">
                            <img src="" alt="Aperçu photo" class="document-thumbnail" id="fieldPhotoThumbnail">
                            <div class="document-info">
                                <div class="document-name" id="fieldPhotoFileName"></div>
                                <div class="document-size" id="fieldPhotoFileSize"></div>
                            </div>
                            <div class="document-actions">
                                <button type="button" class="btn btn-outline" id="fieldPhotoChangeBtn">
                                    <i class="icon" data-lucide="edit"></i> Modifier
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <div></div> <!-- Empty div for spacing -->
                    <button type="button" class="btn btn-primary" id="nextStep1" disabled>
                        Suivant <i class="icon" data-lucide="arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 2 : Numéro de téléphone -->
            <div class="form-step" id="step2">
                <div class="form-group">
                    <label for="phone" class="required-field">Numéro de téléphone</label>
                    <div class="input-group">
                        <span class="prefix">+229</span>
                        <input type="tel" id="phone" placeholder="01XXXXXXXX" pattern="[0-9]{10}">
                    </div>
                    <div class="error-message" id="phoneError">Veuillez entrer un numéro béninois valide (ex:
                        0140999999)</div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="prevStep2">
                        <i class="icon" data-lucide="arrow-left"></i> Retour
                    </button>
                    <button type="button" class="btn btn-primary" id="nextStep2" disabled>
                        Suivant <i class="icon" data-lucide="arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 3 : Sélection de la commune avec autocomplétion -->
            <div class="form-step" id="step3">
                <div class="form-group">
                    <label for="commune" class="required-field">Commune(s) d'activité</label>
                    <div class="commune-autocomplete">
                        <div class="commune-input-container">
                            <input type="text" id="communeInput" class="commune-input"
                                placeholder="Rechercher une commune..." autocomplete="off">
                            <div class="commune-dropdown" id="communeDropdown"></div>
                        </div>
                        <div class="commune-tags" id="communeTags"></div>
                        <input type="hidden" id="selectedCommunes" name="selectedCommunes">
                        <div class="error-message" id="communeError">Veuillez sélectionner au moins une commune</div>
                    </div>
                </div>

                <div class="form-group" id="recapitulatif">
                    <h3>Récapitulatif</h3>
                    <div class="summary-item">
                        <div class="summary-label">Pièce d'identité :</div>
                        <div class="summary-value" id="summaryIdentity"></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Certificat de culture :</div>
                        <div class="summary-value" id="summaryCertificate"></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Photo du champ :</div>
                        <div class="summary-value" id="summaryFieldPhoto"></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Téléphone :</div>
                        <div class="summary-value" id="summaryPhone"></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Commune(s) :</div>
                        <div class="summary-value" id="summaryCommune"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="prevStep3">
                        <i class="icon" data-lucide="arrow-left"></i> Retour
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="icon" data-lucide="check"></i> Soumettre
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les icônes Lucide
        lucide.createIcons();

        // Variables pour stocker les fichiers
        let identityFile = null;
        let certificateFile = null;
        let fieldPhotoFile = null;
        let selectedCommunes = [];
        const communes = [{
                name: "Abomey",
                value: "abomey"
            },
            {
                name: "Abomey-Calavi",
                value: "abomey-calavi"
            },
            {
                name: "Adjohoun",
                value: "adjohoun"
            },
            {
                name: "Adjarra",
                value: "adjarra"
            },
            {
                name: "Agbangnizoun",
                value: "agbangnizoun"
            },
            {
                name: "Aguégués",
                value: "aguegues"
            },
            {
                name: "Allada",
                value: "allada"
            },
            {
                name: "Aplahoué",
                value: "aplahoue"
            },
            {
                name: "Athiémé",
                value: "athieme"
            },
            {
                name: "Avrankou",
                value: "avrankou"
            },
            {
                name: "Banikoara",
                value: "banikoara"
            },
            {
                name: "Bassila",
                value: "bassila"
            },
            {
                name: "Bembèrèkè",
                value: "bembereke"
            },
            {
                name: "Bohicon",
                value: "bohicon"
            },
            {
                name: "Bopa",
                value: "bopa"
            },
            {
                name: "Boukoumbé",
                value: "boukoumbe"
            },
            {
                name: "Cotonou",
                value: "cotonou"
            },
            {
                name: "Comè",
                value: "come"
            },
            {
                name: "Covè",
                value: "cove"
            },
            {
                name: "Dassa-Zoumè",
                value: "dassa-zoume"
            },
            {
                name: "Djakotomey",
                value: "djakotomey"
            },
            {
                name: "Dogbo",
                value: "dogbo"
            },
            {
                name: "Grand-Popo",
                value: "grand-popo"
            },
            {
                name: "Glazoué",
                value: "glazoue"
            },
            {
                name: "Houéyogbé",
                value: "houeyogbe"
            },
            {
                name: "Ifangni",
                value: "ifangni"
            },
            {
                name: "Kalalè",
                value: "kalale"
            },
            {
                name: "Kandi",
                value: "kandi"
            },
            {
                name: "Karimama",
                value: "karimama"
            },
            {
                name: "Kérou",
                value: "kerou"
            },
            {
                name: "Kétou",
                value: "ketou"
            },
            {
                name: "Kouandé",
                value: "kouande"
            },
            {
                name: "Kpomassè",
                value: "kpomasse"
            },
            {
                name: "Lalo",
                value: "lalo"
            },
            {
                name: "Lokossa",
                value: "lokossa"
            },
            {
                name: "Malanville",
                value: "malanville"
            },
            {
                name: "Matéri",
                value: "materi"
            },
            {
                name: "Natitingou",
                value: "natitingou"
            },
            {
                name: "N'Dali",
                value: "ndali"
            },
            {
                name: "Nikki",
                value: "nikki"
            },
            {
                name: "Ouaké",
                value: "ouake"
            },
            {
                name: "Ouinhi",
                value: "ouinhi"
            },
            {
                name: "Ouidah",
                value: "ouidah"
            },
            {
                name: "Parakou",
                value: "parakou"
            },
            {
                name: "Péhunco",
                value: "pehunco"
            },
            {
                name: "Pèrèrè",
                value: "perere"
            },
            {
                name: "Pobè",
                value: "pobe"
            },
            {
                name: "Porto-Novo",
                value: "porto-novo"
            },
            {
                name: "Sakété",
                value: "sakete"
            },
            {
                name: "Savalou",
                value: "savalou"
            },
            {
                name: "Savè",
                value: "save"
            },
            {
                name: "Sèmè-Kpodji",
                value: "seme-kpodji"
            },
            {
                name: "So-Ava",
                value: "so-ava"
            },
            {
                name: "Tchaourou",
                value: "tchaourou"
            },
            {
                name: "Toviklin",
                value: "toviklin"
            },
            {
                name: "Tanguiéta",
                value: "tanguieta"
            },
            {
                name: "Toukountouna",
                value: "toukountouna"
            },
            {
                name: "Toffo",
                value: "toffo"
            },
            {
                name: "Zagnanado",
                value: "zagnanado"
            },
            {
                name: "Za-Kpota",
                value: "za-kpota"
            },
            {
                name: "Zè",
                value: "ze"
            },
            {
                name: "Cobly",
                value: "cobly"
            },
            {
                name: "Bonou",
                value: "bonou"
            },
            {
                name: "Agoué",
                value: "agoue"
            },
            {
                name: "Ndali",
                value: "ndali"
            },
            {
                name: "Toucountouna",
                value: "toucountouna"
            },
            {
                name: "Zakpota",
                value: "zakpota"
            },
            {
                name: "Sô-Ava",
                value: "so-ava"
            },
            {
                name: "Ouèssè",
                value: "ouesse"
            }
        ];

        // Éléments DOM pour l'autocomplétion
        const communeInput = document.getElementById('communeInput');
        const communeDropdown = document.getElementById('communeDropdown');
        const communeTagsContainer = document.getElementById('communeTags');
        const communeError = document.getElementById('communeError');

        // Gestion de l'autocomplétion (inchangée)
        communeInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            if (searchTerm.length < 1) {
                communeDropdown.classList.remove('show');
                return;
            }
            const filteredCommunes = communes.filter(commune =>
                commune.name.toLowerCase().includes(searchTerm) &&
                !selectedCommunes.includes(commune.value)
            );
            updateCommuneDropdown(filteredCommunes);
        });

        // Mettre à jour la liste déroulante des communes (inchangée)
        function updateCommuneDropdown(communesToShow) {
            communeDropdown.innerHTML = '';
            if (communesToShow.length === 0) {
                const noResults = document.createElement('div');
                noResults.className = 'commune-no-results';
                noResults.textContent = 'Aucune commune trouvée';
                communeDropdown.appendChild(noResults);
            } else {
                communesToShow.forEach(commune => {
                    const item = document.createElement('div');
                    item.className = 'commune-item';
                    item.innerHTML = `
                        <i class="icon" data-lucide="map-pin" style="width: 16px; height: 16px;"></i>
                        <span>${commune.name}</span>
                    `;
                    item.addEventListener('click', function() {
                        if (!selectedCommunes.includes(commune.value)) {
                            selectedCommunes.push(commune.value);
                            updateCommuneTags();
                            updateCommuneSummary();
                            communeError.style.display = 'none';
                            communeInput.value = '';
                            communeDropdown.classList.remove('show');
                            lucide.createIcons();
                        }
                    });
                    communeDropdown.appendChild(item);
                });
            }
            communeDropdown.classList.add('show');
            lucide.createIcons();
        }

        // Fermer la liste déroulante quand on clique ailleurs (inchangée)
        document.addEventListener('click', function(e) {
            if (!communeInput.contains(e.target)) {
                communeDropdown.classList.remove('show');
            }
        });

        // Mettre à jour les tags des communes sélectionnées (inchangée)
        function updateCommuneTags() {
            communeTagsContainer.innerHTML = '';
            selectedCommunes.forEach((communeValue, index) => {
                const commune = communes.find(c => c.value === communeValue);
                if (commune) {
                    const tag = document.createElement('div');
                    tag.className = 'commune-tag';
                    tag.innerHTML = `
                        ${commune.name}
                        <span class="commune-tag-remove" data-index="${index}">
                            <i class="icon" data-lucide="x" style="width: 14px; height: 14px;"></i>
                        </span>
                    `;
                    communeTagsContainer.appendChild(tag);
                }
            });
            document.getElementById('selectedCommunes').value = JSON.stringify(selectedCommunes);
            lucide.createIcons();
            document.querySelectorAll('.commune-tag-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    selectedCommunes.splice(index, 1);
                    updateCommuneTags();
                    updateCommuneSummary();
                });
            });
        }

        // Mettre à jour le récapitulatif des communes (inchangée)
        function updateCommuneSummary() {
            const communeNames = selectedCommunes.map(communeValue => {
                const commune = communes.find(c => c.value === communeValue);
                return commune ? commune.name : '';
            }).filter(name => name !== '');
            document.getElementById('summaryCommune').textContent = communeNames.join(', ');
        }

        // Éléments DOM
        const steps = document.querySelectorAll('.step');
        const formSteps = document.querySelectorAll('.form-step');
        const progressBar = document.querySelector('.progress-bar');
        let currentStep = 1;

        // Étape 1 : Téléversement des documents
        const identityUpload = document.getElementById('identityUpload');
        const identityFileInput = document.getElementById('identityFile');
        const identityPreview = document.getElementById('identityPreview');
        const identityFileName = document.getElementById('identityFileName');
        const identityFileSize = document.getElementById('identityFileSize');
        const identityChangeBtn = document.getElementById('identityChangeBtn');
        const identityError = document.getElementById('identityError');
        const certificateDocGroup = document.getElementById('certificateDocGroup');
        const fieldPhotoGroup = document.getElementById('fieldPhotoGroup');
        const nextStep1Btn = document.getElementById('nextStep1');

        // Étape 2 : Numéro de téléphone
        const phoneInput = document.getElementById('phone');
        const phoneError = document.getElementById('phoneError');
        const prevStep2Btn = document.getElementById('prevStep2');
        const nextStep2Btn = document.getElementById('nextStep2');

        // Étape 3 : Commune
        const prevStep3Btn = document.getElementById('prevStep3');
        const submitBtn = document.querySelector('button[type="submit"]');

        // Récapitulatif
        const summaryIdentity = document.getElementById('summaryIdentity');
        const summaryCertificate = document.getElementById('summaryCertificate');
        const summaryFieldPhoto = document.getElementById('summaryFieldPhoto');
        const summaryPhone = document.getElementById('summaryPhone');

        // Fonction pour afficher une étape (inchangée)
        function showStep(stepNumber) {
            steps.forEach(step => {
                if (parseInt(step.dataset.step) === stepNumber) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
                if (parseInt(step.dataset.step) < stepNumber) {
                    step.classList.add('completed');
                } else {
                    step.classList.remove('completed');
                }
            });
            const progressPercentage = ((stepNumber - 1) / (steps.length - 1)) * 100;
            progressBar.style.width = `${progressPercentage}%`;
            formSteps.forEach(step => {
                if (step.id === `step${stepNumber}`) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });
            currentStep = stepNumber;
        }

        // Fonction pour valider un fichier (inchangée)
        function validateFile(file, allowedTypes, maxSize, isImageOnly = false) {
            const fileType = file.type;
            const fileSize = file.size;
            const fileExtension = file.name.split('.').pop().toLowerCase();
            let typeValid = allowedTypes.some(type => {
                return fileType.includes(type) ||
                    (type === 'jpg' && fileExtension === 'jpg') ||
                    (type === 'jpeg' && fileExtension === 'jpeg') ||
                    (type === 'png' && fileExtension === 'png') ||
                    (type === 'pdf' && fileExtension === 'pdf');
            });
            if (isImageOnly && !fileType.startsWith('image/')) {
                typeValid = false;
            }
            const sizeValid = fileSize <= maxSize;
            return {
                valid: typeValid && sizeValid,
                typeError: !typeValid,
                sizeError: !sizeValid
            };
        }

        // Fonction pour formater la taille du fichier (inchangée)
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i]);
        }

        // Gestion du téléversement de la pièce d'identité (inchangée)
        identityFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const validation = validateFile(file, ['image/jpeg', 'image/png', 'application/pdf'], 2 *
                1024 * 1024);
            if (!validation.valid) {
                identityError.textContent = validation.typeError ?
                    'Format de fichier non supporté. Utilisez JPG, PNG ou PDF.' :
                    'Le fichier est trop volumineux (max 2 Mo).';
                identityError.style.display = 'block';
                identityFileInput.value = '';
                return;
            }
            identityError.style.display = 'none';
            identityFile = file;
            identityFileName.textContent = file.name;
            identityFileSize.textContent = formatFileSize(file.size);
            identityPreview.style.display = 'block';
            certificateDocGroup.style.display = 'block';
            identityUpload.style.pointerEvents = 'none';
            identityUpload.style.opacity = '0.7';
        });

        // Bouton pour modifier la pièce d'identité (inchangée)
        identityChangeBtn.addEventListener('click', function() {
            identityFile = null;
            identityFileInput.value = '';
            identityPreview.style.display = 'none';
            identityUpload.style.pointerEvents = 'auto';
            identityUpload.style.opacity = '1';
            certificateDocGroup.style.display = 'none';
            fieldPhotoGroup.style.display = 'none';
            nextStep1Btn.disabled = true;
        });

        // Gestion du téléversement du certificat (inchangée)
        const certificateFileInput = document.getElementById('certificateFile');
        const certificatePreview = document.getElementById('certificatePreview');
        const certificateFileName = document.getElementById('certificateFileName');
        const certificateFileSize = document.getElementById('certificateFileSize');
        const certificateChangeBtn = document.getElementById('certificateChangeBtn');
        const certificateError = document.getElementById('certificateError');

        certificateFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const validation = validateFile(file, ['image/jpeg', 'image/png', 'application/pdf'], 2 *
                1024 * 1024);
            if (!validation.valid) {
                certificateError.textContent = validation.typeError ?
                    'Format de fichier non supporté. Utilisez JPG, PNG ou PDF.' :
                    'Le fichier est trop volumineux (max 2 Mo).';
                certificateError.style.display = 'block';
                certificateFileInput.value = '';
                return;
            }
            certificateError.style.display = 'none';
            certificateFile = file;
            certificateFileName.textContent = file.name;
            certificateFileSize.textContent = formatFileSize(file.size);
            certificatePreview.style.display = 'block';
            fieldPhotoGroup.style.display = 'block';
            document.getElementById('certificateUpload').style.pointerEvents = 'none';
            document.getElementById('certificateUpload').style.opacity = '0.7';

            // MODIFICATION: Activer le bouton suivant même sans photo du champ
            nextStep1Btn.disabled = false;
        });

        // Bouton pour modifier le certificat (inchangée)
        certificateChangeBtn.addEventListener('click', function() {
            certificateFile = null;
            certificateFileInput.value = '';
            certificatePreview.style.display = 'none';
            document.getElementById('certificateUpload').style.pointerEvents = 'auto';
            document.getElementById('certificateUpload').style.opacity = '1';
            fieldPhotoGroup.style.display = 'none';
            nextStep1Btn.disabled = true;
        });

        // Gestion du téléversement de la photo du champ (modifiée pour être facultative)
        const fieldPhotoFileInput = document.getElementById('fieldPhotoFile');
        const fieldPhotoPreview = document.getElementById('fieldPhotoPreview');
        const fieldPhotoFileName = document.getElementById('fieldPhotoFileName');
        const fieldPhotoFileSize = document.getElementById('fieldPhotoFileSize');
        const fieldPhotoThumbnail = document.getElementById('fieldPhotoThumbnail');
        const fieldPhotoChangeBtn = document.getElementById('fieldPhotoChangeBtn');
        const fieldPhotoError = document.getElementById('fieldPhotoError');

        fieldPhotoFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const validation = validateFile(file, ['image/jpeg', 'image/png'], 3 * 1024 * 1024, true);

            if (!validation.valid) {
                fieldPhotoError.textContent = validation.typeError ?
                    'Format de fichier non supporté. Utilisez JPG ou PNG.' :
                    'Le fichier est trop volumineux (max 3 Mo).';
                fieldPhotoError.style.display = 'block';
                fieldPhotoFileInput.value = '';
                return;
            }

            fieldPhotoError.style.display = 'none';
            fieldPhotoFile = file;

            // Afficher l'aperçu
            fieldPhotoFileName.textContent = file.name;
            fieldPhotoFileSize.textContent = formatFileSize(file.size);
            fieldPhotoPreview.style.display = 'block';

            // Afficher la miniature pour les images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fieldPhotoThumbnail.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }

            // Désactiver le champ si un fichier est déjà présent
            document.getElementById('fieldPhotoUpload').style.pointerEvents = 'none';
            document.getElementById('fieldPhotoUpload').style.opacity = '0.7';
        });

        // Bouton pour modifier la photo du champ (modifiée)
        fieldPhotoChangeBtn.addEventListener('click', function() {
            fieldPhotoFile = null;
            fieldPhotoFileInput.value = '';
            fieldPhotoPreview.style.display = 'none';
            document.getElementById('fieldPhotoUpload').style.pointerEvents = 'auto';
            document.getElementById('fieldPhotoUpload').style.opacity = '1';
            // Ne pas désactiver le bouton suivant car la photo est facultative
        });

        // Bouton suivant étape 1 (modifié)
        nextStep1Btn.addEventListener('click', function() {
            // Mettre à jour le récapitulatif
            summaryIdentity.textContent = identityFile ? identityFile.name : 'Non fourni';
            summaryCertificate.textContent = certificateFile ? certificateFile.name : 'Non fourni';
            summaryFieldPhoto.textContent = fieldPhotoFile ? fieldPhotoFile.name : 'Non fourni';

            showStep(2);
        });

        // Validation du numéro de téléphone (inchangée)
        phoneInput.addEventListener('input', function() {
            const phoneRegex = /^[0-9]{10}$/;
            const isValid = phoneRegex.test(phoneInput.value);
            if (phoneInput.value === '') {
                phoneError.style.display = 'none';
                phoneInput.classList.remove('valid', 'invalid');
                nextStep2Btn.disabled = true;
            } else if (isValid) {
                phoneError.style.display = 'none';
                phoneInput.classList.add('valid');
                phoneInput.classList.remove('invalid');
                nextStep2Btn.disabled = false;
            } else {
                phoneError.style.display = 'block';
                phoneInput.classList.add('invalid');
                phoneInput.classList.remove('valid');
                nextStep2Btn.disabled = true;
            }
        });

        // Bouton précédent étape 2 (inchangée)
        prevStep2Btn.addEventListener('click', function() {
            showStep(1);
        });

        // Bouton suivant étape 2 (inchangée)
        nextStep2Btn.addEventListener('click', function() {
            summaryPhone.textContent = `+229 ${phoneInput.value}`;
            showStep(3);
        });

        // Bouton précédent étape 3 (inchangée)
        prevStep3Btn.addEventListener('click', function() {
            showStep(2);
        });

        // Soumission du formulaire (modifiée pour gérer la photo facultative)
        document.getElementById('documentUploadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Vérification des documents obligatoires
            if (!identityFile || !certificateFile) {
                alert(
                    'Les documents obligatoires (pièce d\'identité et certificat) doivent être fournis');
                return;
            }

            // Afficher un indicateur de chargement
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML =
                '<i class="icon" data-lucide="loader" class="animate-spin"></i> Envoi en cours...';
            lucide.createIcons();

            // Créer FormData
            const formData = new FormData();

            // Ajouter les fichiers obligatoires
            formData.append('identity', identityFile);
            formData.append('certificate', certificateFile);

            // Ajouter la photo du champ si elle existe
            if (fieldPhotoFile) {
                formData.append('field_photo', fieldPhotoFile);
            }

            // Ajouter les autres données
            formData.append('telephone', document.getElementById('phone').value);
            formData.append('communes', JSON.stringify(selectedCommunes));
            formData.append('agriculteur_id',
                <?php echo json_encode($_SESSION['agriculteur_id'] ?? null); ?>);

            // Envoyer via AJAX
            fetch('data/complete_profile.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    // Vérifier d'abord le content-type
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new TypeError("La réponse n'est pas du JSON");
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect || 'index.php';
                    } else {
                        showFormErrors(data.errors);
                        submitBtn.innerHTML = originalBtnText;
                        lucide.createIcons();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Afficher un message d'erreur plus clair
                    alert('Une erreur est survenue lors de la soumission. Veuillez réessayer. Détails: ' +
                        error.message);
                    submitBtn.innerHTML = originalBtnText;
                    lucide.createIcons();
                });
        });

        function showFormErrors(errors) {
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
            });
            if (errors) {
                for (const [field, message] of Object.entries(errors)) {
                    const errorElement = document.getElementById(`${field}Error`);
                    if (errorElement) {
                        errorElement.textContent = message;
                        errorElement.style.display = 'block';
                    }
                }
            }
        }

        // Réinitialiser le formulaire (inchangée)
        identityFileInput.value = '';
        certificateFileInput.value = '';
        fieldPhotoFileInput.value = '';
        phoneInput.value = '';
        selectedCommunes = [];
        updateCommuneTags();
        identityPreview.style.display = 'none';
        certificatePreview.style.display = 'none';
        fieldPhotoPreview.style.display = 'none';
        certificateDocGroup.style.display = 'none';
        fieldPhotoGroup.style.display = 'none';
        nextStep1Btn.disabled = true;
        nextStep2Btn.disabled = true;
        identityUpload.style.pointerEvents = 'auto';
        identityUpload.style.opacity = '1';
        document.getElementById('certificateUpload').style.pointerEvents = 'auto';
        document.getElementById('certificateUpload').style.opacity = '1';
        document.getElementById('fieldPhotoUpload').style.pointerEvents = 'auto';
        document.getElementById('fieldPhotoUpload').style.opacity = '1';
        identityFile = null;
        certificateFile = null;
        fieldPhotoFile = null;
        showStep(1);
    });
    </script>
</body>

</html>