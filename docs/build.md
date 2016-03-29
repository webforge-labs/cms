## Pitfalls

the datepicker.scss in vendor/ is the css file copied and renamed as .scss. Unfortunately bootstrap-datepicker is just available in less.

modules/booostrap-datepicker is always german (yet). It is copied from the german locale from the repo

be sure that the dependencies used from the Builder.js are added to dependencies (because child projects won't install the dev-dependencies from cms)