Contributing to FuelPHP
=======================

Any person or company wanting to contribute to FuelPHP should follow the
following rules to increase the change of the contribution being accepted.

Sign your Work
--------------

We require that all contributors "sign-off" on their commits.  This
certifies that the contribution is your original work, or you have rights to
submit it under the same license, or a compatible license.

Any contribution which contains commits that are not Signed-Off will not be
accepted.

To sign off on a commit you simply use the `--signoff` (or `-s`) option when
committing your changes:

    $ git commit -s -m "Adding a new widget driver for cogs."

This will append the following to your commit message:

    Signed-off-by: Your Name <your@email.com>

By doing this you certify the below:

    Developer's Certificate of Origin 1.1

    By making a contribution to this project, I certify that:

    (a) The contribution was created in whole or in part by me and I
        have the right to submit it under the open source license
        indicated in the file; or

    (b) The contribution is based upon previous work that, to the best
        of my knowledge, is covered under an appropriate open source
        license and I have the right under that license to submit that
        work with modifications, whether created in whole or in part
        by me, under the same open source license (unless I am
        permitted to submit under a different license), as indicated
        in the file; or

    (c) The contribution was provided directly to me by some other
        person who certified (a), (b) or (c) and I have not modified
        it.

    (d) I understand and agree that this project and the contribution
        are public and that a record of the contribution (including all
        personal information I submit with it, including my sign-off) is
        maintained indefinitely and may be redistributed consistent with
        this project or the open source license(s) involved.

#### Quick Tip

If you would like to Sign-Off on all of your commits automatically (not
recommended unless you are 100% sure).  To do this, you can simply create
an alias for `git commit -s`:

    $ git config --global alias.cs 'commit -s'

If you wish to only include this for your current repository, simply leave
the `--global` option off:

    $ git config alias.cs 'commit -s'

Now you can Sign-Off on all of your commits if you commit with `git cs`.

Creating and Submitting Contributions
-------------------------------------

FuelPHP uses the Git version control system and hosts it's repositories on
GitHub.  All contributions are submitted using the GitHub Pull Request
system.  No contributions are accepted via email or the community forums.

### Preparing Your Local Environment

If you don't have Git installed locally, now is the time to do so. You'll find
packages for most operating systems, although for Windows it might be a challenge.

Familiarize yourself with both Git and the Git-Flow tools and principles, and when
done, install Git-Flow using the instructions in the turorial. If you're on
Windows, [this](https://github.com/nvie/gitflow/wiki/Windows) might give you
some pointers.

### Setting Up Your Local Environment

To get started, you first need to Fork the repository you wish to contribute
to on GitHub by visiting the the repository GitHub and clicking the "Fork"
button.

**Note:** We will assume throughout this document that you are contributing
to the _application_ repository.

Once you have setup your Fork you are ready to clone it and start working:

1.  Start by cloning your Fork:

        $ git clone git@github.com:USERNAME/application.git

2.  Next you need to add the original repository as a new remote named
`upstream`:

        $ cd application
        $ git remote add upstream git://github.com/fuelphp/application.git
        $ git fetch upstream

### Developing with Contribution in Mind

When developing with the intent for contribution, there are a few things to
keep in mind:

1.  We use the git-flow system. We suggest you do to, and do your development
    in a feature branch.
2.  We only accept pull requests on the `develop` branch. The only exception is
    if you are contributing to a feature branch created by the FuelPHP development
    team itself, in which case we will accept pull requests on this branch too.
    Discuss this with the team member working on it first, it could be that a
    PR on a feature branch (which per definition is a work-in-progress) is not
    appreciated.

    **Note:** If the contribution is submitted to the wrong branch, you will
    be asked to re-submit on the correct branch.

3.  Ensure you follow the FuelPHP Coding Standards.
4.  Unit Tests must be submitted with the contribution if applicable. All
    tests must pass before a contribution is accepted.
5.  A separate complimentary contribution should be submitted to the `docs`
    repository containing the documentation for your change if applicable.
6.  Write good commit messages (no "Some changes" type messages please).

### Submitting your Contribution

After you are done making all of your changes, you need to push them up to
your Fork on GitHub, but *first* you need to update your branch with any
changes from the original repository.

When updating your branch, you should use `rebase` and not merge in the
changes:

1.  Update your `develop` branch with changes from `upstream`:

        $ git checkout develop
        $ git fetch upstream
        $ git merge upstream/develop

2.  Rebase your branch with the `develop` branch:

        $ git checkout FEATURE_BRANCH
        $ git rebase develop

    You may get merge conflicts while running the `rebase` command.  Resolve
    all conflicts, then continue the `rebase`:

        $ git add ...
        $ git rebase --continue

Once the rebase is complete, you are ready to push your changes up to GitHub:

    $ git push origin FEATURE_BRANCH

Now visit your Fork on GitHub (https://github.com/USERNAME/application), switch to
your new branch, then click the "Pull Request" button.

In your Pull Request message you should give as much detail as you can,
including any related links (Issues, Forum Threads, etc.).

### After Submission

After submission, you may need to make changes after discussions on the Pull
Request.  If this is the case, there are a few things you need to be aware
of or do.

1.  After making your changes and committing them locally, you need to
    Rebase your branch with
    the the `upstream/develop` branch:

        $ git rebase -f upstream/develop

2.  You will need to Force Push your changes up to GitHub.  This will
    automatically update your Pull Request:

        $ git push -f origin FEATURE_BRANCH

Once we are satisfied, we will merge and close your Pull Request.

### Resources

* [Git Reference](http://gitref.org/)
* [Git-flow tutorial](http://yakiloo.com/getting-started-git-flow/)
* [FuelPHP GitHub](https://github.com/fuelphp)

If you have any questions, you can usually find at least one of the core
developers in our IRC channel: #fuelphp on FreeNode
