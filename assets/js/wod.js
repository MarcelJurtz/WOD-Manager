class Checkbox {
    constructor(container, element) {
        this.container = container;
        this.element = element;
        this.label = element.querySelectorAll('.label')[0].innerHTML;
        this.checked = element.getElementsByTagName("input")[0].checked;

        this.element.addEventListener('change', () => {
            this.checked = this.element.getElementsByTagName("input")[0].checked;
            container.sortCheckboxes(this.element, this.children);
        })
    }
}

class CheckboxContainer {

    constructor(element) {
        this.element = element;
        this.children = [];

        this.element.querySelectorAll('.checklist-item').forEach(e => this.children.push(new Checkbox(this, e)))
    }

    sortCheckboxes = () => {
        let childNodes = Array.from(this.children);

        childNodes.sort(function (a, b) {

            if (a.checked && !b.checked) {
                return -1;
            } else if (!a.checked && b.checked) {
                return 1;
            }

            // Else go to the 2nd item
            if (a.label.toLowerCase() < b.label.toLowerCase()) {
                return -1;
            } else if (a.label.toLowerCase() > b.label.toLowerCase()) {
                return 1
            }

            return 0;
        });

        for (let checkbox of childNodes) {
            this.element.appendChild(checkbox.element);
        }
    }
}

let remove_weight = id => document.querySelector('.weight-container[data-weight="' + id + '"]').remove();

let add_weight = function () {
    let template = '';

    let container = document.getElementById("weights");
    container.appendChild(htmlToElement(template));
}

let htmlToElement = function (html) {
    var template = document.createElement('template');
    html = html.trim(); // Never return a text node of whitespace as the result
    template.innerHTML = html;
    return template.content.firstChild;
}

let containerElems = document.querySelectorAll('.checklist-container');
let containers = [];
containerElems.forEach(c => containers.push(new CheckboxContainer(c)));

// Initial sort
containers.forEach(c => c.sortCheckboxes());