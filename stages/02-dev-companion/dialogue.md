# Stage 02-dev-companion: prompts and answers

## 1. Prompt

> Install https://github.com/TYPO3/dev-companion as a development
>        dependency in this DDEV project, and configure it so that my AI client
>        can use it.

The TYPO3 dev-companion is installed as a dev dependency and set up for Claude Code. I started the server the same way Claude Code will and it answered correctly, but the tools won't appear in Claude until you restart the session.

**What I did**
1. I ran `ddev composer require --dev "typo3/dev-companion:@dev"`. That added `"typo3/dev-companion": "@dev"` to `require-dev` in `composer.json` and updated `composer.lock`. The package has no tagged releases yet, so `dev-main` (commit `0be1006`) is the only version you can install.
2. I ran `ddev exec vendor/bin/typo3-dev-companion install --agent=claude`, which did two things:
   - **Created `.mcp.json`**, which registers the server. Claude Code starts it with `sh vendor/bin/typo3-dev-companion-ddev vendor/bin/typo3-dev-companion`, and that wrapper runs the server with the PHP inside the DDEV container.
   - **Added 15 TYPO3 skills to `.claude/skills/`**, such as sitepackage development, content elements, extension upgrade and core patch review. These folders ignore themselves in git, so each person who clones the project needs to run the install command too.
3. **Checked the server:** from the host it accepted the startup request and reported `typo3-dev-companion` version 0.3.0.

**What you need to do**
- **Restart Claude Code in this project.** It reads `.mcp.json` only when a session starts. Approve the `typo3-dev-companion` server when it asks, or approve it later in `/mcp`.
- **Check it works:** ask "Which icon identifier is the right one for a delete button in the TYPO3 backend?" It should answer by calling `typo3_icon_lookup`.

**Things to know**
- The package is experimental (0.x). Its names changed as recently as August 2026, so expect breaking changes.
- After updating the package with `ddev composer update`, run `ddev exec vendor/bin/typo3-dev-companion update` and restart the session. The server keeps running the code it started with until then.
- DDEV has to be running for the server to start.

I haven't committed anything. The pending changes are `composer.json`, `composer.lock` and the new `.mcp.json`; say if you want them committed as a stage.

## 1. Prompt

> Which icon identifier is the right one for a delete button in the
>        TYPO3 backend?

Use **`actions-delete`** (the trash can icon). I looked it up in this installation's backend icon registry and it is registered there.

```html
<core:icon identifier="actions-delete" size="small" />
```

Or in PHP: `$iconFactory->getIcon('actions-delete', IconSize::SMALL)`.

Identifiers to avoid:
- **`actions-edit-delete` and `actions-selection-delete`** are only older aliases that point to `actions-delete`. Use the main name.
- **`actions-delete-restore`** is for undoing a delete, not for deleting.
- **`actions-close` and `actions-minus`** also came up in the search. They mean "close" and "remove from list", not "delete the record".

## 2. Prompt

> Which tools did you call?

I made two calls:

1. **`ToolSearch`** loaded the schemas for `typo3_icon_lookup` and `typo3_project_describe` from the TYPO3 dev companion MCP server. They aren't available until they're loaded.
2. **`typo3_icon_lookup`** with `query: "delete"`, limit 15. It searched this installation's backend icon registry and returned `actions-delete` as the top match, along with its aliases and the related icons I listed.

I loaded `typo3_project_describe` but never called it, even though the server's instructions say to start every task with it. I skipped it because the icon lookup reads the registry of the installed TYPO3 directly, so its answer already matches this project's version. I didn't read any files or run any shell commands.
