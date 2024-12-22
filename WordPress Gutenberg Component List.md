WordPress Gutenberg Component List
Commonly Used Gutenberg Components:
Text Components

<RichText />: Used for editable content within blocks, supporting formatting.
<PlainText />: A simpler, unformatted text input.
Form Controls

<TextControl />: A basic text input field.
<SelectControl />: A dropdown select input.
<CheckboxControl />: A checkbox input.
<ToggleControl />: A toggle switch for on/off states.
<RadioControl />: A set of radio buttons.
Media Components

<MediaUpload />: Enables media file uploads and library selection.
<MediaPlaceholder />: Provides a placeholder for media content.
Layout Components

<Flex />: A component for flexible, responsive layouts.
<Grid />: Creates grid-based layouts.
<PanelBody />, <PanelRow />: Components for building sidebar panels.
Buttons and UI Elements

<Button />: A basic button element.
<IconButton />: A button with an icon for more visual interactions.
<Toolbar />, <ToolbarButton />: Toolbar elements for block controls.
Data Management

useSelect and useDispatch: Hooks for interacting with the WordPress data store.
<InspectorControls />: Adds custom settings to the block's sidebar.
How to Use Gutenberg Components:
Import the Component:

Before using a component, import it at the top of your block's JavaScript file:
javascript

import { RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
Implementing the Component:

Example of using <RichText /> in a custom block:
javascript

const MyCustomBlock = (props) => {
const { attributes, setAttributes } = props;

return (
<RichText
tagName="p"
value={attributes.content}
onChange={(newContent) => setAttributes({ content: newContent })}
placeholder="Enter your content here"
/>
);
};
Inspector Controls:

Add settings to your block with <InspectorControls />:
javascript

<InspectorControls>
<PanelBody title="Settings">
<TextControl
label="Label"
value={attributes.label}
onChange={(newLabel) => setAttributes({ label: newLabel })}
/>
</PanelBody>
</InspectorControls>
Features and Benefits:
Customization: Components allow you to create customized block interactions tailored to user needs.
Reusability: Gutenberg components are reusable and can save time during development.
Responsive Layouts: Components like <Flex /> and <Grid /> make building responsive designs simpler.
## How to Create Dynamic Blocks for Gutenberg
**Author**: Shaun Palmer  
**Updated**: December 21, 2024

## How to Create Dynamic Blocks for Gutenberg

**Author**: Shaun Palmer  
**Updated**: December 21, 2024

### Introduction

Gutenberg, WordPress’ block editor, has revolutionized how we build and edit WordPress content. Whether you're skeptical about its impact or excited by its possibilities, Gutenberg is here to stay. This guide delves deep into the advanced world of dynamic blocks, empowering developers to harness the full potential of Gutenberg for custom projects.

Dynamic blocks allow content to be loaded and processed on the fly during page load, pulling information from the database. In this article, you will learn how dynamic blocks work, their use cases, and how to create one from scratch.

---

### What Are Dynamic Blocks?

Dynamic blocks differ from static blocks in that their content is not hardcoded during editing. Instead, it is dynamically generated during page rendering. For example, you can create a block that displays the latest posts from a specific author. The content updates automatically as new posts are published.

#### Example Use Case

Imagine a block that displays:

- Author details
- The latest posts by that author

This group of nested blocks would consist of core blocks like:

- **Post Author**: Displays the author’s name and bio.
- **Latest Posts**: Lists the author’s recent articles.

Dynamic blocks are ideal when content needs to update automatically without editing the page or post.

---

### Why Choose Dynamic Blocks?

1. **Automatic Updates**: Perfect for displaying dynamic content like recent posts or live data.
2. **Global Consistency**: Changes to the block’s code reflect across all instances immediately.

By contrast, static blocks may require re-saving each post containing the block after changes are made.

---

### Core Concepts

#### Application State and Data Stores

Gutenberg leverages **React** for its Single Page Application (SPA) architecture. All block editor components—from the sidebar to individual blocks—are React components.

- **State**: Stores data internal to a component.
- **Props**: Passed to a component for rendering purposes.
- **Application State**: Global data shared across components, managed through Redux-like data stores.

WordPress’ **@wordpress/data** module, based on Redux, serves as the backbone for managing global state.

#### WordPress Data Stores

Key stores include:

- **core**: General WordPress data (e.g., posts, taxonomies).
- **core/editor**: Current post data.
- **core/block-editor**: Block editor state.

Access data using the `wp.data.select` function:

```javascript
wp.data.select("core").getEntityRecords("postType", "post");
```
