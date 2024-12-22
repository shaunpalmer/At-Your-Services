## How to Create Dynamic Blocks for Gutenberg

**Author**: Shaun Palmer\
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

---

### Setting Up Your Development Environment

1. **Install Node.js**: Ensure Node.js is installed. Check with `node -v`.
2. **Set Up a Local WordPress Site**: Tools like **DevKinsta** or **wp-env** simplify local WordPress setup.
3. **Create a Plugin**: Use the `@wordpress/create-block` package:

```bash
npx @wordpress/create-block
```

---

### Building Your First Dynamic Block

Dynamic blocks involve two key components:

1. **Editor Rendering**: JavaScript handles the block’s appearance in the editor.
2. **Frontend Rendering**: PHP processes and outputs the block’s content during page rendering.

#### Step 1: Register the Block

Update the main plugin file:

```php
function register_dynamic_block() {
    register_block_type(__DIR__ . '/build', [
        'render_callback' => 'render_dynamic_block',
    ]);
}
add_action('init', 'register_dynamic_block');

function render_dynamic_block($attributes) {
    return '<p>Dynamic Block Content</p>';
}
```

#### Step 2: Add Block Attributes

Define block attributes in `block.json`:

```json
{
  "attributes": {
    "numberOfItems": { "type": "number", "default": 5 },
    "displayThumbnail": { "type": "boolean", "default": true }
  }
}
```

#### Step 3: Render in the Editor

In `edit.js`, fetch and display posts:

```javascript
import { useSelect } from "@wordpress/data";
import { useBlockProps } from "@wordpress/block-editor";

export default function Edit({ attributes }) {
  const { numberOfItems } = attributes;

  const posts = useSelect(
    (select) => {
      return select("core").getEntityRecords("postType", "post", {
        per_page: numberOfItems,
      });
    },
    [numberOfItems]
  );

  return (
    <ul {...useBlockProps()}>
      {posts &&
        posts.map((post) => <li key={post.id}>{post.title.rendered}</li>)}
    </ul>
  );
}
```

#### Step 4: Render on the Frontend

Update the `render_dynamic_block` function:

```php
function render_dynamic_block($attributes) {
    $posts = get_posts(['numberposts' => $attributes['numberOfItems']]);

    $output = '<ul>';
    foreach ($posts as $post) {
        $output .= '<li>' . esc_html($post->post_title) . '</li>';
    }
    $output .= '</ul>';

    return $output;
}
```

---

### Enhancements and Advanced Features

#### Adding Sidebar Controls

Enable customization via the block settings sidebar:

```javascript
import { InspectorControls } from "@wordpress/block-editor";
import { PanelBody, RangeControl } from "@wordpress/components";

<InspectorControls>
  <PanelBody title="Block Settings">
    <RangeControl
      label="Number of Posts"
      value={attributes.numberOfItems}
      onChange={(value) => setAttributes({ numberOfItems: value })}
      min={1}
      max={10}
    />
  </PanelBody>
</InspectorControls>;
```

#### Displaying Featured Images

Fetch featured images by enabling `_embed` in your query:

```javascript
const posts = useSelect((select) => {
  return select("core").getEntityRecords("postType", "post", {
    per_page: numberOfItems,
    _embed: true,
  });
});
```

Render the images in your block:

```javascript
{
  posts.map((post) => (
    <img src={post._embedded["wp:featuredmedia"][0].source_url} alt="" />
  ));
}
```

---

### Resources for Further Learning

- [Official WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [@wordpress/create-block Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-create-block/)
- [MDN JavaScript Tutorials](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

---

### Conclusion

Dynamic blocks provide a robust way to integrate dynamic, database-driven content into Gutenberg. By mastering these techniques, you can create powerful custom blocks tailored to your project’s needs. With WordPress constantly evolving, dynamic blocks represent a key skill for modern WordPress developers.

Have you built dynamic blocks? Share your experiences and challenges in the comments below!
