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
