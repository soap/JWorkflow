JWorkFlow
=========

Document based workflow for Joomla CMS 3.x.

Workflows
---------
A workflow is a pre-defined set of tasks that must be performed on a document
during the document life cycle. For example, an article is created, reviewed, approved, then
publshed.

States and Transitions
----------------------
Workflows consist of states and transitions. A state may be defined as a stage in a
document’s lifecycle, such as 'draft' or 'being review'. Each workflow has a starting state,
which is the initial state for any document in a workflow.
Transitions, which may be defined as the way in which documents move between
states, are an esential part of the workflow. Each state can have one or more
transitions, depending on the workflow topography.

