import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    
    connect() {
        const appContainer = document.querySelector('.app-container');
        
        if (appContainer) {
            const tabBinderWrapper = document.querySelector('.tab-binder-wrapper') as HTMLElement;
            const computedStyle = window.getComputedStyle(appContainer);
            
            const marginRight = computedStyle.marginRight;
            
            if (tabBinderWrapper) {
                tabBinderWrapper.style.right = marginRight;
            }
        }

        this.updateLayout();
    }

    toggleTabBinder(event: Event) {
        console.log('toggleTabBinder');
        const tabBinder = event.currentTarget as HTMLElement;
        tabBinder.classList.toggle('active');

        const tabId = tabBinder.dataset.tabBinderTarget;
        const tab = document.getElementById(tabId);
        tab?.classList.toggle('d-none');

        this.updateLayout();
    }

    updateLayout() {
        const rightSection = document.getElementById('right-section');
        const leftSection = document.getElementById('left-section');

        if (rightSection && leftSection) {
            const visibleSubContainers = rightSection.querySelectorAll('.sub-container:not(.d-none)');

            if (visibleSubContainers.length > 0) {
                leftSection.style.width = '60%';
                rightSection.style.width = '38%';
            } else {
                leftSection.style.width = '100%';
                rightSection.style.width = '0%';
            }
        }
    }
}