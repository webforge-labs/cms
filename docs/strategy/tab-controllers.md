# Controller for a tab

When tab-content is loaded into the cms it's mostly connected with some javascript module that needs to be loaded and bound (with knockout). This module is called tab-controller. Tab-controllers are loaded from an inline script appended to the content of the tab. The inline script is loaded, when the content is appended to the dom of the cms-frame.
A tab-controller is a root requirejs call, therefore it needs to be defined in the gulpfile (`cmsBuilder.addTabModule`).

The current state of dependencies loading is complicated because we have hickups in the way the form elements from bootstrap are loaded. They render html with contains component or other bindings that have to be loaded from the tab-controller-module as dependencies. Where should those dependencies be defined? In the current architecture they are defined in the embed call of the form/bootstrap3/body.html.twig.

With the following problems:

- they need to be copied to the gulpfile.js (in the include of addTabModule)
- you need godmode to know wich elements.twig makro needs which js binding

But mostly all those form-macros will be used in the whole cms anyway (except exotic ones). So it might make sense to load them on cms-frame-load anyway. The rest are defined as components and will be loaded from knockout.