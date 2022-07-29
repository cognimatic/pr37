module.exports = {
  "stories": [
    "../stories/**/*.stories.mdx",
    "../stories/**/*.stories.@(json)"
  ],
  "addons": [
	'@lullabot/storybook-drupal-addon',
    "@storybook/addon-links",
    "@storybook/addon-essentials"
  ],
  "framework": "@storybook/server",
  "core": {
    "builder": "@storybook/builder-webpack5"
  }
}