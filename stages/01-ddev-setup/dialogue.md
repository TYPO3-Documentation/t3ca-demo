# Stage 01-ddev-setup: prompts and answers

## 1. Prompt

> Which is the newest stable TYPO3 version?

The newest stable TYPO3 version is **TYPO3 14.3.10**, in the v14.3 LTS line.

- **v14.3 LTS** came out on 21 April 2026 and is the version to use for new projects.
- **14.3.10** is the latest maintenance release I found. Its announcement came out together with 13.4.37, 12.4.51, 11.5.56 and 10.4.62. One source said get.typo3.org listed 14.3.10 as current on 28 October 2026, but I couldn't find the release's exact date.
- **14.3.11** is scheduled for 10 November 2026, so that number will change in two days. Check [get.typo3.org](https://get.typo3.org) for the definitive current version.

I didn't find a stable v15 release yet.

Sources:
- [TYPO3 Maintenance Release Schedule for LTS Versions](https://typo3.com/typo3-cms/development-roadmap/maintenance-releases)
- [TYPO3 News](https://news.typo3.com/)
- [List of Releases for TYPO3 v13 – Get TYPO3](https://get.typo3.org/list/version/13)
- [TYPO3 Roadmap 2026: v14 LTS, Support End & ELTS](https://nitsantech.de/en/blog/typo3-roadmap)
- [TYPO3 v14: New Features, Release Notes & Upgrade Guide 2026](https://t3planet.de/en/blog/typo3-v14/)

## 2. Prompt

> Install it using ddev and composer

TYPO3 **14.3.10** is installed with DDEV and Composer at https://demo.ddev.site. The frontend and the backend both return HTTP 200.

**Backend login**
- URL: https://demo.ddev.site/typo3/
- Username: `admin`
- Password: I generated a random one and saved it in `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/35cca6d0-ad19-4ca1-9b7c-74f0f1c6ca26/scratchpad/admin-password.txt`. This file is only kept for this session, so put the password somewhere permanent. TYPO3 also uses it as the Install Tool password.
- Admin email: I set it to your address, admin@mtosafi-demo.example.

**Setup**
- DDEV project `demo`: PHP 8.4, MariaDB 11.8, document root `public/`.
- Installed with `ddev composer create-project "typo3/cms-base-distribution:^14"`, then `ddev typo3 setup` (database `db`), which also created a root page and a site at `https://demo.ddev.site/`.
- I restarted DDEV after the Composer install so it would write `config/system/additional.php`, which holds the database, mail and trusted-hosts settings.

**Two things to know**
- The Composer install only runs in an empty directory, so I set your `README.md` aside, put it back afterwards, and kept it instead of the one that comes with TYPO3.
- While I was working, another commit, also named "Stage 00-start" (`5d4cc80`), appeared on `main` and added a `design/` folder. At one point git showed `design/docs/news-and-events.md` as changed, but a later diff showed no changes. I didn't touch any of it, but you may want to check what happened.

I haven't committed anything. `.ddev/`, `composer.json`, `composer.lock`, `config/` and `packages/` are new and untracked, ready to commit when you want.

## 3. Prompt

> Dont look at sister projects, only work in here

Understood. I'll only work in `/home/lina-wolf/projects/typo3/t3ca-2026/demo` from now on.

I did look at your other projects twice, both times with `ddev list`, which shows every DDEV project on the machine. I was checking whether the project name `demo` was already taken. That command showed your `typo3-core-*` projects. I didn't read or change any files in them, and everything else I did stayed in this directory. If a name is ever taken in future, I'll just let `ddev config` or `ddev start` report the conflict.

I've saved this as a memory, so it carries over to future sessions in this project.

## 4. Prompt

> open TYPO3 in a browser for me

I ran `ddev launch` to open two browser tabs. They should show:

- **Backend:** https://demo.ddev.site/typo3/
- **Frontend:** https://demo.ddev.site/

I can't confirm the tabs opened. The browser printed "Broken pipe" errors when it started. These often appear when Chromium is started from a script and don't always mean it failed. If nothing opened, paste the URLs into your browser yourself.

Log in with the username `admin`. The password is in `/tmp/claude-1000/-home-lina-wolf-projects-typo3-t3ca-2026-demo/35cca6d0-ad19-4ca1-9b7c-74f0f1c6ca26/scratchpad/admin-password.txt`.

## 5. Prompt

> Your connection is not private

`mkcert -install` reported success, but it didn't actually fix Chrome.

- Your default browser is Google Chrome, which reads trusted certificates from `~/.pki/nssdb`. The mkcert authority is **not** in that store.
- It **is** in your Firefox profile. That's probably why mkcert reported "already installed in the Firefox and/or Chrome/Chromium trust store": it found the Firefox one and stopped there.

I tried to add the authority to Chrome's store myself, but the permission system blocked it because it changes certificate trust. Run this yourself (no sudo needed):

```
! certutil -d sql:$HOME/.pki/nssdb -A -t "C,," -n "mkcert development CA" -i "$(mkcert -CAROOT)/rootCA.pem"
```

Then fully quit Chrome (every window) and reopen https://demo.ddev.site/typo3/. To undo it later, run `certutil -d sql:$HOME/.pki/nssdb -D -n "mkcert development CA"`.

Firefox already trusts the authority, so https://demo.ddev.site/typo3/ should open there without the warning right now.
