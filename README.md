# Element Map

*Element Map* is a Craft CMS plugin that adds a sidebar panel to editing pages with a list of related elements. This allows content administrators to see at a glance the relationships between elements.

## Requirements

* Craft CMS 2.x

## Installation

1. Download the latest version of *Element Map*.
2. Move the `elementmap` directory into the `craft/plugins/` directory.
3. In the Craft control panel, go to *Settings > Plugins*.
4. Find *Element Map* in the list of plugins and click the *Install* button.

## Usage

When editing an entry or category, for example, *Element X*, you'll find a sidebar panel with two columns.

1. **Referenced By** lists the elements which contain *Element X* somewhere in their fields. These elements could be entries, categories, tags, assets, users, or globals.

2. **This References** lists the elements which *Element X* contains somewhere in its fields. These elements could be entries, categories, tags, assets, or users.

Both lists provide links to the related elements for quick access.

*Element Map* supports the [Super Table](https://github.com/verbb/super-table) plugin.

---

*Built for [Craft CMS](https://craftcms.com/) by [Charlie Development](http://charliedev.com/)*
