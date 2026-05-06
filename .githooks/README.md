# Git hooks (optional)

This folder holds repository-local hooks so commits stay aligned with workspace rules (no automatic `Co-authored-by:` trailers from the IDE).

## Enable

From the repository root:

```bash
git config core.hooksPath .githooks
```

Hooks must be executable:

```bash
chmod +x .githooks/commit-msg
```

## `commit-msg`

Strips lines matching `Co-authored-by: Cursor` so commit messages stay single-author unless you add credit intentionally.

To bypass hooks for one commit: `git commit --no-verify`.
