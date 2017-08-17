# Tags Plus

An extension for the contao tag module offering a frontend form field among other things.

## Features

### Eval options

Name | Description | Example
---- | ----------- | -------
optionsTable | Used for displaying other options than the ones linked to the table key in the dca | 'optionsTable' => 'tl_member'

### Frontend fields

Name | Description
---- | -----------
FormTags | A frontend representation for the tags module. Currently only a select field

### Backend fields

Name | Description
---- | -----------
TagFieldPlus | Enhances the normal tag field with the opportunity to override the table

### Modules

Name | Description
---- | -----------
ModuleTagCloudPlus | Fixes an issue in the tag cloud (active tags didn't have the "active" class); additional sql and where sql can be added

### Helper classes

Name | Description
---- | -----------
TagsPlus | Provides methods for a more convenient tag access (save and load)