export function initRecipeForm() {
    document.querySelectorAll('[data-collection-add]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-collection-add');
            const container = document.getElementById(targetId);
            const prototype = container?.getAttribute('data-prototype');
            const index = container?.querySelectorAll('.collection-item').length ?? 0;
            if (!container || !prototype) {
                return;
            }

            const isSteps = targetId === 'steps-collection';
            const itemClass = isSteps
                ? 'collection-item collection-item--step'
                : 'collection-item collection-item--ingredient';

            const html = prototype.replace(/__name__/g, String(index));
            const wrapper = document.createElement('div');
            wrapper.className = itemClass;

            if (isSteps) {
                const temp = document.createElement('div');
                temp.innerHTML = html;
                const fields = Array.from(temp.children);
                const orderField = fields.find((el) => el.querySelector('[id$="_stepOrder"]')) || fields[0];
                const rest = fields.filter((f) => f !== orderField);
                const stepFields = document.createElement('div');
                stepFields.className = 'step-fields';
                rest.forEach((f) => stepFields.appendChild(f));
                if (orderField) {
                    wrapper.appendChild(orderField);
                }
                wrapper.appendChild(stepFields);
            } else {
                wrapper.innerHTML = html;
            }

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-item';
            removeBtn.setAttribute('aria-label', isSteps ? 'Remove step' : 'Remove ingredient');
            removeBtn.textContent = '×';
            wrapper.appendChild(removeBtn);

            container.appendChild(wrapper);
            bindRemove(wrapper);
        });
    });

    document.querySelectorAll('.collection-item').forEach(bindRemove);

    function bindRemove(item) {
        const btn = item.querySelector('.remove-item');
        btn?.addEventListener('click', () => item.remove());
    }
}
