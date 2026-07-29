/**
 * Modern Ajax Category Selector Library
 */
class CategorySelector {
    /**
     * @param {string} selector - CSS selector for the wrapper element
     * @param {object} config - Configuration object
     */
    constructor(selector, config = {}) {
        this.wrapper = document.querySelector(selector);
        if (!this.wrapper) {
            console.error('CategorySelector: Target element not found:', selector);
            return;
        }

        this.wrapper.classList.add('category-selector');

        // Default Configuration
        this.config = {
            inputName: 'category_id',
            initialValue: '',
            initialData: null, // For SSR (Server-Side Rendering / Edit Mode)
            debugId: null,     // ID of element to show debug info
            extraParams: {},   // e.g. { langId: 1 }
            debounceTime: 400,      // Wait 400ms after typing stops
            minSearchLength: 2,     // Start searching after 2 chars
            urls: {
                fetch: '/api/categories',        // GET ?parent_id=X
                search: '/api/categories/search' // GET ?q=keyword
            },

            // UI Strings (Ideally passed via PHP)
            ux: {
                placeholder: 'Select Category...',
                searchPlaceholder: 'Search...',
                noResults: 'No results found.',
                loading: 'Loading...',
                searching: 'Searching...'
            },

            onSelect: (item) => {
            }
        };

        // Deep Merge Config
        this.config = {...this.config, ...config};
        if (config.urls) this.config.urls = {...this.config.urls, ...config.urls};
        if (config.ux) this.config.ux = {...this.config.ux, ...config.ux};
        if (config.extraParams) this.config.extraParams = {...this.config.extraParams, ...config.extraParams};

        this.isOpen = false;
        this.selectedItem = null;
        this.searchTimeout = null; // Timer for debounce

        this.buildDOM();

        this.toggleBtn = this.wrapper.querySelector('.category-toggle');
        this.dropdown = this.wrapper.querySelector('.category-dropdown');
        this.listContainer = this.wrapper.querySelector('.category-list');
        this.searchInput = this.wrapper.querySelector('.search-input');
        this.hiddenInput = this.wrapper.querySelector(`input[name="${this.config.inputName}"]`);
        this.toggleText = this.toggleBtn.querySelector('.placeholder-text');

        this.initEvents();
        this.initData();
    }

    /**
     * Public: Reloads the component with new parameters (e.g. Language change)
     */
    reload(newConfig = {}) {
        if (newConfig.extraParams) {
            this.config.extraParams = {...this.config.extraParams, ...newConfig.extraParams};
        }

        // Reset State
        this.selectedItem = null;
        this.hiddenInput.value = '';
        if (this.config.debugId) {
            const debugEl = document.getElementById(this.config.debugId);
            if (debugEl) debugEl.textContent = '-';
        }

        // Reset UI Text
        this.toggleText.textContent = this.config.ux.placeholder;
        this.toggleText.classList.remove('selected-text');
        this.toggleText.classList.add('placeholder-text');

        // Clear search input
        this.searchInput.value = '';

        // Clear List & Reload Roots
        this.listContainer.innerHTML = '';
        this.loadRootCategories();
    }

    // Internal Logic
    buildDOM() {
        this.wrapper.innerHTML = `
            <input type="hidden" name="${this.config.inputName}" value="${this.config.initialValue}">
            <div class="category-toggle">
                <span class="placeholder-text">${this.config.ux.placeholder}</span>
               <i class="ki-solid ki-down fs-3 text-gray-600"></i>
            </div>
            <div class="category-dropdown">
                <div class="search-box-wrapper">
                    <input type="text" name="cat-search-name" class="search-input" placeholder="${this.config.ux.searchPlaceholder}" autocomplete="off">
                </div>
                <div class="category-list"></div>
            </div>
        `;
    }

    initEvents() {
        this.toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle();
        });

        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) this.close();
        });

        // Search with Debounce
        this.searchInput.addEventListener('input', (e) => {
            const keyword = e.target.value.trim();

            if (this.searchTimeout) clearTimeout(this.searchTimeout);

            this.searchTimeout = setTimeout(() => {
                if (keyword.length === 0) {
                    this.handleSearch('');
                    return;
                }
                if (keyword.length < this.config.minSearchLength) {
                    return;
                }
                this.handleSearch(keyword);
            }, this.config.debounceTime);
        });
    }

    initData() {
        this.listContainer.innerHTML = '';

        if (this.config.allowNone) {
            this.renderItem({id: 0, name: this.config.ux.noneOption, hasChildren: false, depth: 0}, this.listContainer);
        }

        if (this.config.initialData && Array.isArray(this.config.initialData)) {
            this.renderTree(this.config.initialData, this.listContainer);
            const val = this.hiddenInput.value;
            if (val) {
                const item = this.findItemInTree(this.config.initialData, val);
                if (item) this.updateSelectionUI(item);
            }
        } else {
            this.loadRootCategories();
        }
    }

    async fetchCategories(parentId) {
        const queryParams = {...this.config.extraParams};
        if (parentId) queryParams.parent_id = parentId;

        try {
            const url = new URL(this.config.urls.fetch, window.location.origin);
            Object.keys(queryParams).forEach(key => url.searchParams.append(key, queryParams[key]));

            const response = await fetch(url);
            if (!response.ok) throw new Error('Network error');

            const json = await response.json();
            return Array.isArray(json) ? json : (json.data || []);

        } catch (error) {
            console.error("CategorySelector Fetch Error:", error);
            return [];
        }
    }

    async searchDatabase(keyword) {
        const queryParams = {
            q: keyword,
            ...this.config.extraParams
        };

        try {
            const url = new URL(this.config.urls.search, window.location.origin);
            Object.keys(queryParams).forEach(key => url.searchParams.append(key, queryParams[key]));

            const response = await fetch(url);
            if (!response.ok) throw new Error('Network error');

            const json = await response.json();
            return Array.isArray(json) ? json : (json.data || []);

        } catch (error) {
            console.error("CategorySelector Search Error:", error);
            return [];
        }
    }

    async loadRootCategories() {
        this.listContainer.innerHTML =
            `<div class="loading-spinner p-5">
                <div class="spinner-border text-primary" style="width: 1rem; height: 1rem;" role="status">
                    <span class="visually-hidden"></span>
                </div>&nbsp;${this.config.ux.loading}
            </div>`;
        const roots = await this.fetchCategories(null);
        this.listContainer.innerHTML = '';

        if (this.config.allowNone) {
            this.renderItem({id: 0, name: this.config.ux.noneOption, hasChildren: false, depth: 0}, this.listContainer);
        }

        if (!roots || roots.length === 0) {
            this.listContainer.innerHTML = `<div class="no-results">${this.config.ux.noResults}</div>`;
            return;
        }
        roots.forEach(item => this.renderItem(item, this.listContainer));
    }

    renderTree(nodes, container) {
        nodes.forEach(item => {
            const hasPreloadedChildren = item.children && item.children.length > 0;
            const nodeElements = this.renderItem(item, container, hasPreloadedChildren);

            if (hasPreloadedChildren) {
                const childrenContainer = nodeElements.childrenContainer;
                childrenContainer.dataset.loaded = 'true';
                this.renderTree(item.children, childrenContainer);
            }
        });
    }

    renderItem(item, containerElement, forceExpand = false) {
        const node = document.createElement('div');
        node.className = 'tree-node';
        node.setAttribute('data-id', item.id);

        const row = document.createElement('div');
        row.className = 'tree-row';

        if (this.hiddenInput.value == item.id) {
            row.classList.add('selected');
            setTimeout(() => {
                if (this.isOpen) row.scrollIntoView({block: 'nearest'});
            }, 100);
        }

        const childrenContainer = document.createElement('div');
        childrenContainer.className = 'tree-children';
        childrenContainer.dataset.loaded = 'false';

        if (forceExpand) {
            row.setAttribute('aria-expanded', 'true');
            childrenContainer.classList.add('show');
        }

        // Convert to boolean safely
        const hasChildren = (item.hasChildren == true || item.hasChildren == 1 || item.hasChildren == "1");
        // Check if it's a root element (depth 0)
        const isRoot = (item.depth == 0 || item.depth == "0");

        // LOGIC:
        // 1. If it has children -> Always show Arrow.
        // 2. If it has NO children BUT is Root -> Show Spacer (to align with other roots).
        // 3. If it has NO children AND is Sub -> Show Nothing (lean left).
        if (hasChildren || isRoot) {
            const toggleWrapper = document.createElement('div');
            toggleWrapper.className = 'toggle-icon-wrapper';

            if (hasChildren) {
                if (!row.hasAttribute('aria-expanded')) {
                    row.setAttribute('aria-expanded', 'false');
                }
                toggleWrapper.innerHTML = '<i class="ki-solid ki-right fs-3 toggle-icon"></i>';
                toggleWrapper.classList.add('has-icon');
            } else {
                // Only roots get a spacer if they have no children
                toggleWrapper.classList.add('is-spacer');
            }
            row.appendChild(toggleWrapper);
        }

        const nameSpan = document.createElement('span');
        nameSpan.textContent = item.id > 0 ? `${item.name} · #${item.id}` : item.name;
        row.appendChild(nameSpan);

        row.addEventListener('click', (e) => {
            if (hasChildren && e.target.closest('.toggle-icon-wrapper')) {
                e.preventDefault();
                e.stopPropagation();
                this.toggleNode(item, row, childrenContainer);
            } else {
                this.selectCategory(item, row);
            }
        });

        node.appendChild(row);
        node.appendChild(childrenContainer);
        containerElement.appendChild(node);

        return {node, row, childrenContainer};
    }

    async toggleNode(item, rowElement, childrenContainer) {
        const isExpanded = rowElement.getAttribute('aria-expanded') === 'true';

        if (isExpanded) {
            rowElement.setAttribute('aria-expanded', 'false');
            childrenContainer.classList.remove('show');
        } else {
            rowElement.setAttribute('aria-expanded', 'true');
            childrenContainer.classList.add('show');

            if (childrenContainer.dataset.loaded === 'false') {
                childrenContainer.innerHTML =
                    `<div class="loading-spinner">
                        <div class="spinner-border text-primary" style="width: 1rem; height: 1rem;" role="status">
                          <span class="visually-hidden"></span>
                        </div>
                         ${this.config.ux.loading}
                    </div>`;

                const children = await this.fetchCategories(item.id);
                childrenContainer.innerHTML = '';
                childrenContainer.dataset.loaded = 'true';

                if (!children || children.length === 0) {
                    // No subcategories found
                } else {
                    children.forEach(child => this.renderItem(child, childrenContainer));
                }
            }
        }
    }

    selectCategory(item, rowElement) {
        this.selectedItem = item;
        this.hiddenInput.value = item.id;
        this.updateSelectionUI(item, rowElement);
        if (typeof this.config.onSelect === 'function') {
            this.config.onSelect(item);
        }
        this.close();
    }

    updateSelectionUI(item, rowElement) {
        this.toggleText.textContent = item.name;
        this.toggleText.classList.remove('placeholder-text');
        this.toggleText.classList.add('selected-text');

        this.wrapper.querySelectorAll('.tree-row').forEach(el => el.classList.remove('selected'));

        if (!rowElement) {
            const node = this.wrapper.querySelector(`.tree-node[data-id="${item.id}"]`);
            if (node) rowElement = node.querySelector('.tree-row');
        }

        if (rowElement) rowElement.classList.add('selected');

        if (this.config.debugId) {
            const debugEl = document.getElementById(this.config.debugId);
            if (debugEl) debugEl.textContent = `${item.name} (ID: ${item.id})`;
        }
    }

    async handleSearch(keyword) {
        if (keyword.length < 1) {
            this.listContainer.innerHTML = '';
            if (this.config.initialData) {
                this.renderTree(this.config.initialData, this.listContainer);
            } else {
                this.loadRootCategories();
            }
            return;
        }

        this.listContainer.innerHTML =
            `<div class="loading-spinner p-5">
                <div class="spinner-border text-primary" style="width: 1rem; height: 1rem;" role="status">
                    <span class="visually-hidden"></span>
                </div>&nbsp;${this.config.ux.searching}
            </div>`;

        const results = await this.searchDatabase(keyword);
        this.listContainer.innerHTML = '';

        if (!results || results.length === 0) {
            this.listContainer.innerHTML = `<div class="no-results">${this.config.ux.noResults}</div>`;
            return;
        }

        results.forEach(item => {
            const div = document.createElement('div');
            div.className = 'search-item';
            const breadcrumb = item.path_text || '';
            div.innerHTML = `<span class="breadcrumb-text">${breadcrumb}</span><span class="item-text">${item.name} · #${item.id}</span>`;
            div.addEventListener('click', () => this.selectCategory(item, null));
            this.listContainer.appendChild(div);
        });
    }

    findItemInTree(nodes, id) {
        for (const node of nodes) {
            if (node.id == id) return node;
            if (node.children) {
                const found = this.findItemInTree(node.children, id);
                if (found) return found;
            }
        }
        return null;
    }

    toggle() {
        this.isOpen ? this.close() : this.open();
    }

    open() {
        this.isOpen = true;
        this.dropdown.classList.add('show');
        this.toggleBtn.classList.add('active');
        this.searchInput.focus();
    }

    close() {
        this.isOpen = false;
        this.dropdown.classList.remove('show');
        this.toggleBtn.classList.remove('active');
    }
}