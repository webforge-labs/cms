define(['jquery', 'bootstrap-select'], function(jQuery, bootstrapSelect) {

  jQuery.fn.selectpicker.defaults = {
    noneSelectedText: 'Bitte wählen...',
    noneResultsText: 'Keine Ergebnisse für {0}',
    countSelectedText: '{0} von {1} ausgewählt',
    maxOptionsText: ['Limit erreicht ({n} {var} max.)', 'Gruppen-Limit erreicht ({n} {var} max.)', ['Eintrag', 'Einträge']],
    multipleSeparator: ', '
  };

  return bootstrapSelect;
});