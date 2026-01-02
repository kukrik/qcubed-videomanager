# QCubed VideoEmbed Plugin

## VideoEmbed for QCubed-4

The **VideoEmbed** plugin is a reusable QCubed-4 component designed to simplify embedding external videos into content
managed through the CKEditor 4 HTML editor.  
It provides a clean and structured way to select, display, store, and remove video embeds while keeping business logic
fully under the developer’s control.

The plugin is intended for use in articles, news items, pages, or any other content type where rich media support is required.

![Image of kukrik](screenshot/videomanager_screenhots_1.png?raw=true)
![Image of kukrik](screenshot/videomanager_screenhots_2.png?raw=true)
![Image of kukrik](screenshot/videomanager_screenhots_3.png?raw=true)

---

## Features

- Integration with **CKEditor 4**
- Video selection and embedding via an external video manager
- Automatic embed sanitization using `cleanEmbedCode()`
- Clear separation between UI events and application logic
- Reusable and extensible architecture
- Custom QCubed event handling (e.g. delete actions)
- Compatible with **PHP 8.3+**

---

## How It Works

The VideoEmbed plugin appears on the **right side of the CKEditor interface** and allows editors to:

- Select a video
- Preview the embedded video
- Remove or replace an existing video

The plugin focuses only on **UI interaction and data transport**.  
All validation, permission checks, confirmation dialogs, and database operations are intentionally left to the developer.

---

## Database Requirements

To use the VideoEmbed plugin, your database table must contain at least the following columns:

- `media_id`
- `video_embed`

How these fields are validated, stored, or processed is entirely application-specific.

---

## Usage Notes

This repository contains **simplified examples** intended to demonstrate how the plugin works.
The examples do not include full validation, permission checks, or advanced error handling.

It is the responsibility of the developer to implement:

- User permission checks
- Confirmation dialogs (if required)
- Business logic and database consistency
- Additional UI or workflow constraints

---

## Editor Integration

For CKEditor 4 integration examples, see:

- `ckeditor.php`
- `ckeditor2.php`
- `ckeditor3.php`

For a more advanced media workflow, you may also refer to:

- `mediafinder.php` (from the QCubed FileManager plugin)

---

## Requirements

- **PHP 8.3 or newer**
- **QCubed-4**

Optional but recommended dependencies:

```bash
composer require qcubed-4/plugin-bootstrap
composer require kukrik/qcubed-videomanager