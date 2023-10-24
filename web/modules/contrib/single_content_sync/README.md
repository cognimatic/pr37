# Single Content Sync

A simple way to export/import a node content with all entity references.

## Export content

### Which entity references can be exported?

Here is a current list of supported entity references:

- Taxonomy term
- Node
- Block content
- User
- Media
- Paragraphs

This list can be extended by you, see how to do it below.

### Can I extend exporting of my custom entity type?

Yes! You can implement a new `SingleContentSyncBaseFieldsProcessor` plugin to support your custom entity.

Check a few examples of existing plugins at `src/Plugin/SingleContentSyncBaseFieldsProcessor`.

### Is there any other way I could extend exported entity?

The module would dispatch the `ExportEvent` event before exporting an entity. You can use it to alter the output
before exporting.

## Import content

### Can I extend importing of my custom entity?

In most cases, new entity types would be handled automatically. Similar to exporting, there's an `ImportEvent` which
could be subscribed to alter the imported entity before it's saved.

### Can I import my content on deploy?

Yes! Please use the importer service and hook_update_N or similar to do it.

```php
function example_update_9001() {
  $file_path = \Drupal::service('extension.list.module')
    ->getPath('example') . '/assets/homepage.yml';

  \Drupal::service('single_content_sync.importer')
    ->importFromFile($file_path);
}
```

If you would like to import content from a generated zip file,
use the following code:

```php
function example_update_9001() {
  $file_path = \Drupal::service('extension.list.module')
    ->getPath('example') . '/assets/homepage.zip';

  \Drupal::service('single_content_sync.importer')
    ->importFromZip($file_path);
}
```

## Implementing extra field types

You can implement `SingleContentSyncFieldProcessor` plugin for your custom field type or contrib field types
which aren't supported by the module yet - patches are welcome!

Check a few examples of existing plugins at `src/Plugin/SingleContentSyncFieldProcessor`.

The plugin should include both import and export logic (and it's pretty much straightforward).

## Drush commands

You can use Drush commands to export and import your content.

### Export

To export content you can use `drush content:export`. By default, the command will export all entities of type `Node` at the following location: `DRUPAL_ROOT/scs-export`.
You can customize the execution of the command by passing it some parameters and options.
The first parameter will change the entity types being exported (e.g. `taxonomy_term`, `block_content`, etc.).
The second parameter will specify an output path from DRUPAL_ROOT.
For example: `drush content:export block_content ./export-folder` will export all entities of type `block_content` in the `DRUPAL_ROOT/export-folder` directory (if the export-folder directory does not exist, a new one will be created).

The following options can also be passed to the command:

-   `--translate` if used, the export will also contain the translated content
-   `--assets` if used, the export will also contain all necessary assets
-   `--all-content` if used, the export will contain all entities of all entity types
-   `--dry-run` if used, the terminal will show an example output without performing the export
-   `--entities` if used, only the entities passed (using entity id) will be in the export. Usage: `drush content:export --entities="1,4,7"`. if `--all-content` is used, it will take priority over this option.

### Import

To import content you can use `drush content:import`. The import command requires a `path` parameter to import content from.
The `path` parameter is a relative path to the DRUPAL_ROOT folder.
For example: `drush content:import export-folder/content-bulk-export.zip` will import the contents of a zip folder in the following location `DRUPAL_ROOT/export-folder/content-bulk-export.zip`.

## Documentation

Check out the guide to see the module’s overview and the guidelines for using it

https://www.drupal.org/docs/contributed-modules/single-content-sync
