Git Feature Branch Workflow
===========================

Git Feature Workflow

This is the iterim process we'll be using for our SDN software development.  Basically we'll be using the [_Feature Branch Workflow_](https://www.atlassian.com/git/tutorials/comparing-workflows/feature-branch-workflow), we'll summarize the workflow here.

This workflow will be used for both _onos-app-samples_ and _mcast-dev-test_.

### Clone the repository

~~~
git clone git@bitbucket.org:sdnmcast/mcast-dev-test.git
~~~

### Checkout or create a feature branch

Make the new branch based off master.

~~~
git checkout -b feature-branch
~~~

#### Make Edits

Make whatever changes or edits you want.  Make sure your edits compile and pass whatever little tests you want.

#### Commit Your changes

~~~
commit -am "I just added a certain piece of this feature"
~~~

#### Push your commits, including your branch

~~~
git push -u origin feature-branch
~~~

You can iterate through these previous three steps any number of times.  You should expect to commit and push at least once a day, if not many times through the day, depending on what you are doing.

_NOTE:_ you only have to push with the '-u' option the first time.  Subsequent pushes you can just a push to send your changeset to the central repository.

~~~
git push 
~~~

### Pull Requests for Code reviews and Questions

We are going to use _pull requests_ for code reviews, but we can also use a pull request to discuss new or modified code.

When you have a hunk of code that you would like to have reviewed and pulled back into master, or you just want to have a discussion about some code, create a _pull request_ from the bitbucket.org interface.

You can read [how to create a pull request here](https://www.atlassian.com/git/tutorials/making-a-pull-request/).

#### About pull requests

Pull requests will allow people to see your changes, leave comments, etc.  The comments section will allow you to respond to peoples questions.  The entire discussion will be captured as such.

You as well as others will be able to pull your branch make changes and commit against them as well.  All this will be captured by the pull request. 

### Publishing changes to the master branch

Once the changes have all been accepted, you or the author can commit the changes.  To do this you will need to checkout the master branch, make sure the master branch is up to date, then finally push the changes up to the main repository.

~~~
git checkout master
git pull
git pull origin features
git push
~~~

You can now delete the branch.
