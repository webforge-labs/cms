# Content Manager Blocks

A block has a `name`, `label`, `component` and some options.

The label will be displayed to the user, in the `Inhalt hinzufügen`-Dropdown and as a headline of the panel. The label might then be changed from user (not yet implemented).
The name is the name of the blockType. Therefore it is added as `type` to the structure in contentstream:

the config is like this:
```
contentManager: {
  blockTypes: [
    {
      name: 'markdown',
      label: 'Fließtext',
      component: 'markdown',
      icon: 'align-left'
    }
  ]
}
``` 

the data entry would be like this
```
contentStreamProperty: {
  blocks: [
    {
      label: 'Fließtext',
      type: 'markdown',
      content: 'the value of the textbox',
      uuid: 'xxx-xxxxx-xxxxxxx'
    }
  ]
}
```

