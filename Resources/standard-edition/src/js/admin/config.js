define(function() {

  return {
    Dropbox: {
      appKey: 'xxx' // @FIXME
    },

    // if you need a new Module for a block add the component to js/admin/blocks and register it in content-manager-blocks.js

    contentManager: {
      blockTypes: [
        {
          name: 'markdown',
          label: 'Fließtext',
          component: 'markdown',
          icon: 'align-left'
        },
        {
          name: 'intro',
          label: 'Introtext',
          component: 'markdown',
          icon: 'font'
        },
        /*
        {
          name: 'video',
          label: 'Video'
        },
        */
        {
          name: 'polaroid-stripe',
          label: 'Polaroids (hochkant)',
          component: 'polaroid-stripe-vertical',
          icon: 'image fa-rotate-90'
        },
        {
          name: 'polaroid-stripe-horizontal',
          label: 'Polaroids (waagerecht)',
          icon: 'image'
        },
        {
          label: 'Interview Frage-Antwort',
          name: 'interview-qa',
          icon: 'question-circle'
        },
        {
          name: 'credits',
          label: 'Quellenangaben',
          component: 'markdown',
          icon: 'copyright'
        }
      ]
    }
  };

});