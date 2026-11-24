# AI meets TYPO3 — T3CA 2026

The talk is shown as recorded stages rather than live, so it needs neither the
AI's time nor the internet.

- `demo/` is the TYPO3 project the AI builds, a git repository of its own. Each
  stage is a tag `stage-<NN-slug>` in it.
- `stages/<NN-slug>/` keeps what git cannot: the recorded AI session
  (`session.cast`), the database (`db.sql.gz`), `fileadmin.tar.gz`, and
  `notes.md` with the prompt and what to point out.
- `./stage` moves between them. `./stage help` lists the commands.

## Recording, at home

    sudo apt install asciinema        # once
    ./stage record 01-ddev-setup      # work with the AI in demo/, then type exit
    ./stage record 02-site-package
    ./stage list

`./stage save <NN-slug>` saves the current state without a recording.

## Before travelling

Switch the Wi-Fi off, then:

    ./stage rehearse

It switches to every stage in order and checks that the site answers.

## On stage

    ./stage play 2      # replay the AI session of stage 2, sped up
    ./stage go 2        # show its result in the browser
    ./stage next

`SPEED=8 ./stage play 2` replays faster. While Claude thinks, only the spinner
moves; `play` squeezes each such stretch to 1.5 seconds (`squeeze-cast`).
`THINK=5` keeps more of it, `THINK=0` plays the recording unchanged.

## Reading instead of watching

`stages/<stage>/dialogue.md` holds the prompts of a stage and the answer Claude
gave at the end of each turn, without tool calls and their output. `./stage
save` writes it from Claude Code's session log, which only exists on the
computer the stage was recorded on. To write it again:

    ./extract-dialogue stages/12-contact-form          # final answers only
    ./extract-dialogue stages/12-contact-form --all    # also Claude's notes in between

## Handout

[`handout/handout.pdf`](handout/handout.pdf): the talk's takeaways on four pages, with the prompts,
the commands and the official links. `handout/make-pdf` writes it from `handout/handout.html`.

## Repository

Both parts live in https://github.com/TYPO3-Documentation/t3ca-demo:

- `main` and the `stage-*` tags hold `demo/`, the TYPO3 project.
- `presenter` holds this folder: `./stage`, the recordings and the database
  exports. The presenter notes (`stages/*/notes.md`) stay local until after the
  talk.

To set it up on another machine:

    git clone -b presenter git@github.com:TYPO3-Documentation/t3ca-demo.git t3ca-2026
    git clone git@github.com:TYPO3-Documentation/t3ca-demo.git t3ca-2026/demo

From stage 02 on, the dev companion's skills are not in git: they are copies
that go stale. After `./stage go 2` or later, generate them once with
`ddev exec vendor/bin/typo3-dev-companion update`.

After `./stage save`, push both: `git push` here, and
`git -C demo push && git -C demo push --tags`.

## License

GPL-2.0-or-later, like TYPO3. See `LICENSE`.
