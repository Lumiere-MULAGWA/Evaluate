// ========== UTILITIES ========== //
const Utils = {
    // Sélecteur d'éléments
    $ (selector) {
        return document.querySelector(selector);
    },
    
    $$ (selector) {
        return document.querySelectorAll(selector);
    },
    
    // Vérifier si un élément existe
    exists (element) {
        return element !== null && element !== undefined;
    },
    
    // Ajouter une classe
    addClass (element, className) {
        if (this.exists(element)) {
            element.classList.add(className);
        }
    },
    
    // Supprimer une classe
    removeClass (element, className) {
        if (this.exists(element)) {
            element.classList.remove(className);
        }
    },
    
    // Basculer une classe
    toggleClass (element, className) {
        if (this.exists(element)) {
            element.classList.toggle(className);
        }
    },
    
    // Animations
    fadeIn (element, duration = 300) {
        if (!this.exists(element)) return;
        
        element.style.opacity = '0';
        element.style.display = 'block';
        
        let start = null;
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = timestamp - start;
            
            element.style.opacity = Math.min(progress / duration, 1);
            
            if (progress < duration) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    },
    
    fadeOut (element, duration = 300) {
        if (!this.exists(element)) return;
        
        let start = null;
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = timestamp - start;
            
            element.style.opacity = Math.max(1 - (progress / duration), 0);
            
            if (progress < duration) {
                requestAnimationFrame(animate);
            } else {
                element.style.display = 'none';
            }
        };
        
        requestAnimationFrame(animate);
    }
};

// ========== NOTIFICATIONS ========== //
const Notifications = {
    container: null,
    
    init() {
        // Créer le conteneur de notifications s'il n'existe pas
        if (!Utils.$('.notifications-container')) {
            this.container = document.createElement('div');
            this.container.className = 'notifications-container';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                pointer-events: none;
            `;
            document.body.appendChild(this.container);
        } else {
            this.container = Utils.$('.notifications-container');
        }
    },
    
    show(message, type = 'info', duration = 5000) {
        this.init();
        
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} animate-slide-in-right`;
        notification.style.cssText = `
            margin-bottom: 10px;
            pointer-events: auto;
            min-width: 300px;
            box-shadow: var(--shadow-lg);
        `;
        
        const icon = this.getIcon(type);
        notification.innerHTML = `
            <i class="${icon}"></i>
            <span>${message}</span>
            <button class="btn-close" style="margin-left: auto; background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        `;
        
        // Ajouter l'événement de fermeture
        const closeBtn = notification.querySelector('.btn-close');
        closeBtn.addEventListener('click', () => {
            this.remove(notification);
        });
        
        this.container.appendChild(notification);
        
        // Auto-suppression après la durée spécifiée
        if (duration > 0) {
            setTimeout(() => {
                this.remove(notification);
            }, duration);
        }
        
        return notification;
    },
    
    remove(notification) {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    },
    
    getIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            danger: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    },
    
    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    },
    
    error(message, duration = 5000) {
        return this.show(message, 'danger', duration);
    },
    
    warning(message, duration = 5000) {
        return this.show(message, 'warning', duration);
    },
    
    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }
};

// ========== FORM VALIDATION ========== //
const FormValidator = {
    rules: {
        required: (value) => value.trim() !== '',
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        minLength: (value, length) => value.length >= length,
        maxLength: (value, length) => value.length <= length,
        number: (value) => !isNaN(value) && value !== '',
        range: (value, min, max) => {
            const num = parseFloat(value);
            return !isNaN(num) && num >= min && num <= max;
        }
    },
    
    messages: {
        required: 'Ce champ est obligatoire',
        email: 'Veuillez entrer une adresse email valide',
        minLength: 'Ce champ doit contenir au moins {0} caractères',
        maxLength: 'Ce champ ne peut pas contenir plus de {0} caractères',
        number: 'Veuillez entrer un nombre valide',
        range: 'La valeur doit être comprise entre {0} et {1}'
    },
    
    validate(form) {
        const inputs = form.querySelectorAll('[data-validate]');
        let isValid = true;
        
        inputs.forEach(input => {
            const rules = input.dataset.validate.split('|');
            const value = input.value;
            
            // Supprimer les classes de validation précédentes
            Utils.removeClass(input, 'is-valid');
            Utils.removeClass(input, 'is-invalid');
            
            // Supprimer les messages d'erreur précédents
            const errorMsg = input.parentNode.querySelector('.invalid-feedback');
            if (errorMsg) {
                errorMsg.remove();
            }
            
            for (let rule of rules) {
                const [ruleName, ...params] = rule.split(':');
                
                if (ruleName === 'required' && !this.rules.required(value)) {
                    this.showError(input, this.messages.required);
                    isValid = false;
                    break;
                } else if (ruleName === 'email' && value && !this.rules.email(value)) {
                    this.showError(input, this.messages.email);
                    isValid = false;
                    break;
                } else if (ruleName === 'minLength' && value) {
                    const length = parseInt(params[0]);
                    if (!this.rules.minLength(value, length)) {
                        this.showError(input, this.messages.minLength.replace('{0}', length));
                        isValid = false;
                        break;
                    }
                } else if (ruleName === 'maxLength' && value) {
                    const length = parseInt(params[0]);
                    if (!this.rules.maxLength(value, length)) {
                        this.showError(input, this.messages.maxLength.replace('{0}', length));
                        isValid = false;
                        break;
                    }
                } else if (ruleName === 'range' && value) {
                    const min = parseFloat(params[0]);
                    const max = parseFloat(params[1]);
                    if (!this.rules.range(value, min, max)) {
                        this.showError(input, this.messages.range.replace('{0}', min).replace('{1}', max));
                        isValid = false;
                        break;
                    }
                }
            }
            
            // Si aucune erreur, marquer comme valide
            if (isValid || !input.classList.contains('is-invalid')) {
                Utils.addClass(input, 'is-valid');
            }
        });
        
        return isValid;
    },
    
    showError(input, message) {
        Utils.addClass(input, 'is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.style.cssText = `
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: var(--danger-color);
        `;
        errorDiv.textContent = message;
        
        input.parentNode.appendChild(errorDiv);
    }
};

// ========== EVALUATION FORM ========== //
const EvaluationForm = {
    init() {
        this.setupScoreInputs();
        this.setupFormValidation();
        this.setupSelectAll();
    },
    
    setupScoreInputs() {
        const scoreInputs = Utils.$$('input[type="number"][name*="note"]');
        
        scoreInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                const value = parseFloat(e.target.value);
                
                // Supprimer les classes précédentes
                Utils.removeClass(e.target, 'low-score');
                Utils.removeClass(e.target, 'high-score');
                
                if (!isNaN(value)) {
                    if (value <= 50) {
                        Utils.addClass(e.target, 'low-score');
                    } else if (value >= 80) {
                        Utils.addClass(e.target, 'high-score');
                    }
                }
            });
            
            // Validation en temps réel
            input.addEventListener('blur', (e) => {
                const value = parseFloat(e.target.value);
                if (isNaN(value) || value < 0 || value > 100) {
                    Utils.addClass(e.target, 'is-invalid');
                    Notifications.error('La note doit être comprise entre 0 et 100');
                } else {
                    Utils.removeClass(e.target, 'is-invalid');
                    Utils.addClass(e.target, 'is-valid');
                }
            });
        });
    },
    
    setupFormValidation() {
        const forms = Utils.$$('form[data-validate="true"]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                if (FormValidator.validate(form)) {
                    // Afficher un indicateur de chargement
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="loading"></span> Enregistrement...';
                    submitBtn.disabled = true;
                    
                    // Simuler l'envoi (ou utiliser fetch pour un vrai envoi AJAX)
                    setTimeout(() => {
                        // Restaurer le bouton
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        
                        // Soumettre le formulaire
                        form.submit();
                    }, 1000);
                } else {
                    Notifications.error('Veuillez corriger les erreurs dans le formulaire');
                }
            });
        });
    },
    
    setupSelectAll() {
        const selectAllBtn = Utils.$('#select-all-employees');
        const employeeCheckboxes = Utils.$$('input[name="employes[]"]');
        
        if (selectAllBtn && employeeCheckboxes.length > 0) {
            selectAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                
                const allChecked = Array.from(employeeCheckboxes).every(cb => cb.checked);
                
                employeeCheckboxes.forEach(checkbox => {
                    checkbox.checked = !allChecked;
                });
                
                selectAllBtn.textContent = allChecked ? 'Sélectionner tout' : 'Désélectionner tout';
            });
        }
    }
};

// ========== DASHBOARD ========== //
const Dashboard = {
    init() {
        this.animateStats();
        this.setupCharts();
    },
    
    animateStats() {
        const statValues = Utils.$$('.stat-value');
        
        statValues.forEach(stat => {
            const finalValue = parseInt(stat.textContent);
            let currentValue = 0;
            const increment = Math.ceil(finalValue / 50);
            
            const animate = () => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    stat.textContent = finalValue;
                } else {
                    stat.textContent = currentValue;
                    requestAnimationFrame(animate);
                }
            };
            
            // Démarrer l'animation quand l'élément est visible
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animate();
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            observer.observe(stat);
        });
    },
    
    setupCharts() {
        // Ici vous pouvez intégrer Chart.js ou une autre librairie de graphiques
        // Pour l'instant, nous ajoutons juste des graphiques basiques en CSS
        const chartContainers = Utils.$$('.chart-container');
        
        chartContainers.forEach(container => {
            const data = JSON.parse(container.dataset.chart || '{}');
            this.createSimpleChart(container, data);
        });
    },
    
    createSimpleChart(container, data) {
        // Graphique en barres simple avec CSS
        const maxValue = Math.max(...Object.values(data));
        
        Object.entries(data).forEach(([label, value]) => {
            const bar = document.createElement('div');
            bar.className = 'chart-bar';
            
            const height = (value / maxValue) * 100;
            
            bar.innerHTML = `
                <div class="bar-fill" style="height: ${height}%; background: var(--primary-color); transition: height 1s ease;"></div>
                <div class="bar-label">${label}</div>
                <div class="bar-value">${value}</div>
            `;
            
            container.appendChild(bar);
        });
    }
};

// ========== THEME TOGGLE ========== //
const ThemeToggle = {
    init() {
        const toggleBtn = Utils.$('#theme-toggle');
        
        if (toggleBtn) {
            toggleBtn.addEventListener('click', this.toggle.bind(this));
            
            // Appliquer le thème sauvegardé
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            }
        }
    },
    
    toggle() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        Notifications.info(`Thème ${newTheme === 'dark' ? 'sombre' : 'clair'} activé`);
    }
};

// ========== SEARCH & FILTER ========== //
const SearchFilter = {
    init() {
        this.setupSearch();
        this.setupFilters();
    },
    
    setupSearch() {
        const searchInputs = Utils.$$('[data-search]');
        
        searchInputs.forEach(input => {
            const targetSelector = input.dataset.search;
            
            input.addEventListener('input', (e) => {
                this.filterElements(targetSelector, e.target.value);
            });
        });
    },
    
    setupFilters() {
        const filterSelects = Utils.$$('[data-filter]');
        
        filterSelects.forEach(select => {
            const targetSelector = select.dataset.filter;
            
            select.addEventListener('change', (e) => {
                this.filterByAttribute(targetSelector, select.dataset.filterAttribute, e.target.value);
            });
        });
    },
    
    filterElements(selector, searchTerm) {
        const elements = Utils.$$(selector);
        const term = searchTerm.toLowerCase();
        
        elements.forEach(element => {
            const text = element.textContent.toLowerCase();
            const shouldShow = text.includes(term);
            
            element.style.display = shouldShow ? '' : 'none';
        });
    },
    
    filterByAttribute(selector, attribute, value) {
        const elements = Utils.$$(selector);
        
        elements.forEach(element => {
            const attributeValue = element.getAttribute(attribute);
            const shouldShow = !value || attributeValue === value;
            
            element.style.display = shouldShow ? '' : 'none';
        });
    }
};

// ========== EVALUATION MANAGER ========== //
const EvaluationManager = {
    totalCriteria: 0,
    completedCriteria: 0,
    
    init() {
        this.setupEventListeners();
        this.calculateTotalCriteria();
        this.updateProgress();
        this.checkFormValidity();
    },
    
    setupEventListeners() {
        // Écouter les changements de notes
        const ratingInputs = Utils.$$('.rating-input');
        ratingInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.handleRatingChange(input);
                this.updateProgress();
                this.checkFormValidity();
            });
        });
        
        // Bouton d'aperçu
        const previewBtn = Utils.$('#previewBtn');
        if (previewBtn) {
            previewBtn.addEventListener('click', () => {
                this.showPreview();
            });
        }
        
        // Validation du formulaire
        const form = Utils.$('#evaluationForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm()) {
                    e.preventDefault();
                    Notifications.error('Veuillez compléter toutes les évaluations avant de soumettre.');
                }
            });
        }
    },
    
    calculateTotalCriteria() {
        const employeeSections = Utils.$$('.employee-evaluation-section');
        const criteriaPerEmployee = Utils.$$('.criteria-section').length / employeeSections.length;
        this.totalCriteria = employeeSections.length * criteriaPerEmployee;
    },
    
    handleRatingChange(input) {
        const employeeId = input.dataset.employee;
        const criteriaKey = input.dataset.criteria;
        
        // Mettre à jour le style de la section des critères
        const criteriaSection = input.closest('.criteria-section');
        Utils.addClass(criteriaSection, 'completed');
        
        // Animation de validation
        const ratingOption = input.closest('.rating-option');
        Utils.addClass(ratingOption, 'selected');
        
        // Mettre à jour la progression de l'employé
        this.updateEmployeeProgress(employeeId);
    },
    
    updateEmployeeProgress(employeeId) {
        const employeeSection = Utils.$(`[data-employee-id="${employeeId}"]`);
        if (!employeeSection) return;
        
        const totalCriteria = employeeSection.querySelectorAll('.criteria-section').length;
        const completedCriteria = employeeSection.querySelectorAll('.criteria-section.completed').length;
        
        const progressText = employeeSection.querySelector('.employee-progress-text');
        const progressBar = employeeSection.querySelector('.employee-progress-bar');
        
        if (progressText) {
            progressText.textContent = `${completedCriteria} / ${totalCriteria} critères`;
        }
        
        if (progressBar) {
            const percentage = (completedCriteria / totalCriteria) * 100;
            progressBar.style.width = `${percentage}%`;
        }
    },
    
    updateProgress() {
        this.completedCriteria = Utils.$$('.criteria-section.completed').length;
        
        const progressCounter = Utils.$('.progress-counter');
        const progressBar = Utils.$('#evaluationProgressBar');
        
        if (progressCounter) {
            progressCounter.textContent = `${this.completedCriteria} / ${this.totalCriteria} critères évalués`;
        }
        
        if (progressBar) {
            const percentage = this.totalCriteria > 0 ? (this.completedCriteria / this.totalCriteria) * 100 : 0;
            progressBar.style.width = `${percentage}%`;
        }
    },
    
    checkFormValidity() {
        const submitBtn = Utils.$('#submitBtn');
        if (!submitBtn) return;
        
        const isComplete = this.completedCriteria === this.totalCriteria;
        
        if (isComplete) {
            submitBtn.disabled = false;
            Utils.removeClass(submitBtn, 'btn-secondary');
            Utils.addClass(submitBtn, 'btn-success');
        } else {
            submitBtn.disabled = true;
            Utils.addClass(submitBtn, 'btn-secondary');
            Utils.removeClass(submitBtn, 'btn-success');
        }
    },
    
    validateForm() {
        return this.completedCriteria === this.totalCriteria;
    },
    
    showPreview() {
        const previewContent = this.generatePreviewContent();
        const modal = Utils.$('#previewModal');
        const modalBody = Utils.$('#previewContent');
        
        if (modalBody) {
            modalBody.innerHTML = previewContent;
        }
        
        if (modal) {
            Utils.addClass(modal, 'show');
        }
    },
    
    generatePreviewContent() {
        let html = '<div class="preview-summary">';
        
        const employeeSections = Utils.$$('.employee-evaluation-section');
        
        employeeSections.forEach(section => {
            const employeeId = section.dataset.employeeId;
            const employeeName = section.querySelector('.employee-details h2').textContent;
            const employeeEmail = section.querySelector('.employee-meta').textContent.trim();
            
            html += `
                <div class="preview-employee">
                    <h3><i class="fas fa-user"></i> ${employeeName}</h3>
                    <p class="preview-employee-info">${employeeEmail}</p>
                    <div class="preview-criteria">
            `;
            
            const criteriaSections = section.querySelectorAll('.criteria-section');
            let totalScore = 0;
            let criteriaCount = 0;
            
            criteriaSections.forEach(criteriaSection => {
                const criteriaTitle = criteriaSection.querySelector('.criteria-title h3').textContent;
                const selectedRating = criteriaSection.querySelector('.rating-input:checked');
                const comment = criteriaSection.querySelector('.comment-textarea').value;
                
                if (selectedRating) {
                    const score = parseInt(selectedRating.value);
                    totalScore += score;
                    criteriaCount++;
                    
                    html += `
                        <div class="preview-criteria-item">
                            <div class="preview-criteria-header">
                                <span class="criteria-name">${criteriaTitle}</span>
                                <span class="criteria-score ${this.getScoreClass(score)}">${score}/20</span>
                            </div>
                            ${comment ? `<p class="criteria-comment">${comment}</p>` : ''}
                        </div>
                    `;
                }
            });
            
            const averageScore = criteriaCount > 0 ? (totalScore / criteriaCount).toFixed(1) : 0;
            
            html += `
                    </div>
                    <div class="preview-summary-score">
                        <strong>Moyenne générale: <span class="${this.getScoreClass(averageScore)}">${averageScore}/20</span></strong>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        return html;
    },
    
    getScoreClass(score) {
        if (score <= 6) return 'score-insufficient';
        if (score <= 10) return 'score-passable';
        if (score <= 14) return 'score-good';
        if (score <= 18) return 'score-very-good';
        return 'score-excellent';
    }
};

// ========== MODAL FUNCTIONS ========== //
function closeModal(modalId) {
    const modal = Utils.$('#' + modalId);
    if (modal) {
        Utils.removeClass(modal, 'show');
    }
}

// Fermer les modales en cliquant à l'extérieur
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        Utils.removeClass(e.target, 'show');
    }
});

// ========== INITIALISATION ========== //
document.addEventListener('DOMContentLoaded', () => {
    // Initialiser tous les modules
    Notifications.init();
    EvaluationForm.init();
    Dashboard.init();
    ThemeToggle.init();
    SearchFilter.init();
    EvaluationManager.init();
    
    // Ajouter des animations aux éléments
    const animatedElements = Utils.$$('.animate-on-load');
    animatedElements.forEach((element, index) => {
        setTimeout(() => {
            Utils.addClass(element, 'animate-fade-in');
        }, index * 200);
    });
    
    // Smooth scroll pour les liens d'ancrage
    const anchorLinks = Utils.$$('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const target = Utils.$(link.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Tooltips simples
    const tooltips = Utils.$$('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = e.target.dataset.tooltip;
            tooltip.style.cssText = `
                position: absolute;
                background: var(--gray-800);
                color: var(--white);
                padding: 0.5rem 0.75rem;
                border-radius: var(--border-radius);
                font-size: var(--font-size-sm);
                z-index: 1000;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = e.target.getBoundingClientRect();
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
            
            setTimeout(() => tooltip.style.opacity = '1', 10);
            
            e.target.addEventListener('mouseleave', () => {
                tooltip.remove();
            }, { once: true });
        });
    });
    
    console.log('📊 Evaluate - Système d\'évaluation du personnel initialisé avec succès!');
});

// ========== EXPORTS POUR UTILISATION EXTERNE ========== //
window.EvaluateApp = {
    Utils,
    Notifications,
    FormValidator,
    EvaluationForm,
    Dashboard,
    ThemeToggle,
    SearchFilter
};
