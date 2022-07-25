'use strict';
class Tournamatch {

    constructor() {
        this.events = {};
    }

    param(object, prefix) {
        let str = [];
        for (let prop in object) {
            if (object.hasOwnProperty(prop)) {
                let k = prefix ? prefix + "[" + prop + "]" : prop;
                let v = object[prop];
                str.push((v !== null && typeof v === "object") ? this.param(v, k) : encodeURIComponent(k) + "=" + encodeURIComponent(v));
            }
        }
        return str.join("&");
    }

    event(eventName) {
        if (!(eventName in this.events)) {
            this.events[eventName] = new EventTarget(eventName);
        }
        return this.events[eventName];
    }

    autocomplete(input, dataCallback) {
        new Tournamatch_Autocomplete(input, dataCallback);
    }

    ucfirst(s) {
        if (typeof s !== 'string') return '';
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    ordinal_suffix(number) {
        const remainder = number % 100;

        if ((remainder < 11) || (remainder > 13)) {
            switch (remainder % 10) {
                case 1: return 'st';
                case 2: return 'nd';
                case 3: return 'rd';
            }
        }
        return 'th';
    }

    tabs(element) {
        const tabs = element.getElementsByClassName('tournamatch-nav-link');
        const panes = document.getElementsByClassName('tournamatch-tab-pane');
        const clearActive = () => {
            Array.prototype.forEach.call(tabs, (tab) => {
                tab.classList.remove('tournamatch-nav-active');
                tab.ariaSelected = false;
            });
            Array.prototype.forEach.call(panes, pane => pane.classList.remove('tournamatch-tab-active'));
        };
        const setActive = (targetId) => {
            const targetTab = document.querySelector('a[href="#' + targetId + '"].tournamatch-nav-link');
            const targetPaneId = targetTab && targetTab.dataset && targetTab.dataset.target || false;

            if (targetPaneId) {
                clearActive();
                targetTab.classList.add('tournamatch-nav-active');
                targetTab.ariaSelected = true;

                document.getElementById(targetPaneId).classList.add('tournamatch-tab-active');
            }
        };
        const tabClick = (event) => {
            const targetTab = event.currentTarget;
            const targetPaneId = targetTab && targetTab.dataset && targetTab.dataset.target || false;

            if (targetPaneId) {
                setActive(targetPaneId);
                event.preventDefault();
            }
        };

        Array.prototype.forEach.call(tabs, (tab) => {
            tab.addEventListener('click', tabClick);
        });

        if (location.hash) {
            setActive(location.hash.substr(1));
        } else if (tabs.length > 0) {
            setActive(tabs[0].dataset.target);
        }
    }

}

//trn.initialize();
if (!window.trn_obj_instance) {
    window.trn_obj_instance = new Tournamatch();
}
export let trn = window.trn_obj_instance;

window.addEventListener('load', function () {
    const tabViews = document.getElementsByClassName( 'tournamatch-nav' );

    Array.from(tabViews).forEach((tab) => {
        trn.tabs(tab);
    });
}, false);

class Tournamatch_Autocomplete {

    // currentFocus;
    //
    // nameInput;
    //
    // self;

    constructor(input, dataCallback) {
        // this.self = this;
        this.nameInput = input;

        this.nameInput.addEventListener("input", () => {
            let a, b, i, val = this.nameInput.value;//this.value;
            let parent = this.nameInput.parentNode;//this.parentNode;

            // let p = new Promise((resolve, reject) => {
            //     /* need to query server for names here. */
            //     let xhr = new XMLHttpRequest();
            //     xhr.open('GET', options.api_url + 'players/?search=' + val + '&per_page=5');
            //     xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            //     xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            //     xhr.onload = function () {
            //         if (xhr.status === 200) {
            //             // resolve(JSON.parse(xhr.response).map((player) => {return { 'value': player.id, 'text': player.name };}));
            //             resolve(JSON.parse(xhr.response).map((player) => {return player.name;}));
            //         } else {
            //             reject();
            //         }
            //     };
            //     xhr.send();
            // });
            dataCallback(val).then((data) => {//p.then((data) => {
                console.log(data);

                /*close any already open lists of autocompleted values*/
                this.closeAllLists();
                if (!val) { return false;}
                this.currentFocus = -1;

                /*create a DIV element that will contain the items (values):*/
                a = document.createElement("DIV");
                a.setAttribute("id", this.nameInput.id + "-autocomplete-list");
                a.setAttribute("class", "autocomplete-items");

                /*append the DIV element as a child of the autocomplete container:*/
                parent.appendChild(a);

                /*for each item in the array...*/
                for (i = 0; i < data.length; i++) {
                    let text, value;

                    /* Which format did they give us. */
                    if (typeof data[i] === 'object') {
                        text = data[i]['text'];
                        value = data[i]['value'];
                    } else {
                        text = data[i];
                        value = data[i];
                    }

                    /*check if the item starts with the same letters as the text field value:*/
                    if (text.substr(0, val.length).toUpperCase() === val.toUpperCase()) {
                        /*create a DIV element for each matching element:*/
                        b = document.createElement("DIV");
                        /*make the matching letters bold:*/
                        b.innerHTML = "<strong>" + text.substr(0, val.length) + "</strong>";
                        b.innerHTML += text.substr(val.length);

                        /*insert a input field that will hold the current array item's value:*/
                        b.innerHTML += "<input type='hidden' value='" + value + "'>";

                        b.dataset.value = value;
                        b.dataset.text = text;

                        /*execute a function when someone clicks on the item value (DIV element):*/
                        b.addEventListener("click", (e) => {
                            console.log(`item clicked with value ${e.currentTarget.dataset.value}`);

                            /* insert the value for the autocomplete text field: */
                            this.nameInput.value = e.currentTarget.dataset.text;
                            this.nameInput.dataset.selectedId = e.currentTarget.dataset.value;

                            /* close the list of autocompleted values, (or any other open lists of autocompleted values:*/
                            this.closeAllLists();

                            this.nameInput.dispatchEvent(new Event('change'));
                        });
                        a.appendChild(b);
                    }
                }
            });
        });

        /*execute a function presses a key on the keyboard:*/
        this.nameInput.addEventListener("keydown", (e) => {
            let x = document.getElementById(this.nameInput.id + "-autocomplete-list");
            if (x) x = x.getElementsByTagName("div");
            if (e.keyCode === 40) {
                /*If the arrow DOWN key is pressed,
                 increase the currentFocus variable:*/
                this.currentFocus++;
                /*and and make the current item more visible:*/
                this.addActive(x);
            } else if (e.keyCode === 38) { //up
                /*If the arrow UP key is pressed,
                 decrease the currentFocus variable:*/
                this.currentFocus--;
                /*and and make the current item more visible:*/
                this.addActive(x);
            } else if (e.keyCode === 13) {
                /*If the ENTER key is pressed, prevent the form from being submitted,*/
                e.preventDefault();
                if (this.currentFocus > -1) {
                    /*and simulate a click on the "active" item:*/
                    if (x) x[this.currentFocus].click();
                }
            }
        });

        /*execute a function when someone clicks in the document:*/
        document.addEventListener("click", (e) => {
            this.closeAllLists(e.target);
        });
    }

    addActive(x) {
        /*a function to classify an item as "active":*/
        if (!x) return false;
        /*start by removing the "active" class on all items:*/
        this.removeActive(x);
        if (this.currentFocus >= x.length) this.currentFocus = 0;
        if (this.currentFocus < 0) this.currentFocus = (x.length - 1);
        /*add class "autocomplete-active":*/
        x[this.currentFocus].classList.add("autocomplete-active");
    }

    removeActive(x) {
        /*a function to remove the "active" class from all autocomplete items:*/
        for (let i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }

    closeAllLists(element) {
        console.log("close all lists");
        /*close all autocomplete lists in the document,
         except the one passed as an argument:*/
        let x = document.getElementsByClassName("autocomplete-items");
        for (let i = 0; i < x.length; i++) {
            if (element !== x[i] && element !== this.nameInput) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }
}

// First, checks if it isn't implemented yet.
if (!String.prototype.format) {
    String.prototype.format = function() {
        const args = arguments;
        return this.replace(/{(\d+)}/g, function(match, number) {
            return typeof args[number] !== 'undefined'
                ? args[number]
                : match
                ;
        });
    };
}