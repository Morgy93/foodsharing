#!/usr/bin/python

from typing import List, Tuple
import requests
import re
import sys

FILTER_LABELS = {
	'for::FS Community': 'Allgemein',
	'for::BIEB': 'Betriebsverantwortliche',
	'for::Ambassador': 'Botschafter\\*innen',
	'for::Special working groups': 'Spezielle Arbeitsgruppen',
	'for::Orga only': 'Orga'
}
API_URL = 'https://gitlab.com/api/v4'
FIND_IMAGE_REGEXP = "\!\[[^]]*\]\([^\)]*\)"


# Returns the project name and baseUrl
def findProject(projectId: int) -> Tuple[str, str]:
	url = API_URL + '/projects/{0}?private_token={1}'
	response = requests.get(url.format(projectId, personalToken)).json()
	return response['name'], response['web_url']


# Returns the ID and name of a milestone for its number in the project, or None if the number does not exist
def findMilestoneID(projectId: int, milestoneNumber: int) -> Tuple[int, str]:
	url = API_URL + '/projects/{0}/milestones/?private_token={1}&iids[]={2}'
	milestones = requests.get(url.format(projectId, personalToken, milestoneNumber)).json()
	matches = [m for m in milestones if m['iid'] == milestoneNumber]
	if len(matches) > 0:
		return matches[0]['id'], matches[0]['title']
	else:
		return None


# Requests all data from a paginated endpoint and returns the combined list
def requestPaginated(url, printSteps: bool = True) -> List:
	endOfPages = False
	page = 1
	combined = []
	while not endOfPages:
		data = requests.get(url + '&page={0}'.format(page)).json()
		if len(data) > 0:
			combined += data
			page += 1
			if printSteps:
				print('.', end='', flush=True)
		else:
			endOfPages = True
			if printSteps:
				print()
	return combined


# Lists all merge requests of a milestone
def listMergeRequests(projectId: int, milestoneId: int) -> List:
	url = API_URL + '/projects/{0}/milestones/{1}/merge_requests?private_token={2}'
	return requestPaginated(url.format(projectId, milestoneId, personalToken))


# Extracts the first part of the markdown text that has the specific title or None if it was not found. The title can
# be any headline from h1 to h6.
def extractTextPart(text: str, partTitle: str) -> str:
	# find title
	try:
		idx = text.index(partTitle)
		part = text[idx+len(partTitle)+1:]
	except:
		return None

	try:
		# find title of next paragraph
		idx = part.index('#')
		part = part[:idx]
	except:
		pass
	return part


# Returns the release notes from the description, or None if none was found
def extractReleaseNotesText(description: str) -> str:
	searchTexts = ['Release notes text in German', 'Release notes text', 'Text Release Notes']
	filterText = '<!-- A short text that will appear in the release notes and describes the change for non-technical people in German. Not always necessary, e.g. not for refactoring. -->'

	# check if a release notes section is present
	part = None
	for s in searchTexts:
		part = extractTextPart(description, s)
		if part is not None:
			break

	if part is None:
		return None

	# remove the placeholder text
	try:
		idx2 = part.index(filterText)
		part = part[0:idx2] + part[idx2+len(filterText)+1:]
	except:
		pass

	part = part.strip()

	# remove enumeration marks if present
	if part.startswith('- '):
		part = part[2:].strip()

	return None if len(part) < 1 else part


# Extracts and returns the links to images from a markdown text
def extractImageLinks(text: str, projectUrl: str) -> List[str]:
	if text is None or len(text) < 1:
		return []

	occurences = [s for s in re.findall(FIND_IMAGE_REGEXP, text)]
	paths = [s[s.rfind('(') + 1:-1] for s in occurences]
	for i in range(len(paths)):
		if not paths[i].startswith('http'):
			paths[i] = projectUrl + paths[i]
	return paths


# Removes all images from a markdown text
def removeImageLinks(text: str) -> str:
	if text is None or len(text) < 1:
		return text

	reducedText = text
	found = re.search(FIND_IMAGE_REGEXP, reducedText)
	while found:
		span = found.span()
		reducedText = reducedText[:span[0]] + reducedText[span[1] + 1:]
		found = re.search(FIND_IMAGE_REGEXP, reducedText)
	return reducedText


# Prints info about an MR and optionally the release notes text
def printMRInfo(data, releaseNotesText=None, imageLinks=[], outputFile=None):
	# print(data['title'], file=outputFile)
	if releaseNotesText is not None:
		print(releaseNotesText, file=outputFile)
	print(f'[Referenz: {data["iid"]}]({data["web_url"]})', file=outputFile)
	for link in imageLinks:
		print(f'![image]({link})', file=outputFile)


# input of all parameters
personalToken = input('Gib hier dein personal Token ein (Mehr Infos findest du hier https://gitlab.com/-/profile/personal_access_tokens): ')
projectID = int(input('ID des Gitlab-Projekts (leer lassen für foodsharing=1454647): ') or '1454647')
milestone = int(input('Nummer des Milestones: ') or '20')
filename = input('Ausgabedatei (leer lassen für stdout): ' or None)
print()

# find project
projectName, projectUrl = findProject(projectID)
print("Project '{0}', {1}".format(projectName, projectUrl))

# find milestone
milestoneMatch = findMilestoneID(projectID, milestone)
if milestoneMatch is None:
	print('Milestone ' + str(milestone) + ' not found!')
	exit(1)
else:
	print('Milestone: "' + milestoneMatch[1] + '"')

# open output file
outputFile = None
if filename is not None and len(filename) > 0:
	outputFile = open(filename, 'w')

# find all merge requests in milestone
print('Frage merge requests ab')
mergeRequests = listMergeRequests(projectID, milestoneMatch[0])

# filter out those with specific labels
for label, labelName in FILTER_LABELS.items():
	filteredMRs = list(filter(lambda x: label in x['labels'] and x['state'] == 'merged', mergeRequests))
	print('\n\n## {0} ({1} merge requests)'.format(labelName, len(filteredMRs)), file=outputFile)

	# extract release note texts and image links
	releaseNoteTexts = [extractReleaseNotesText(mr['description']) for mr in filteredMRs]
	imageLinks = [extractImageLinks(text, projectUrl) for text in releaseNoteTexts]
	for i in range(len(releaseNoteTexts)):
		if releaseNoteTexts[i] is not None:
			releaseNoteTexts[i] = removeImageLinks(releaseNoteTexts[i])

	# filter MRs into text / no text
	combined = list(zip(filteredMRs, releaseNoteTexts, imageLinks))
	withText = [c for c in combined if c[1] is not None]
	withoutText = [c for c in combined if c[1] is None]

	# print details
	if len(withText) > 0:
		print('### Text vorhanden ({0} MRs):'.format(len(withText)), file=outputFile)
		for m in withText:
			printMRInfo(*m, outputFile=outputFile)
			print('\n---', file=outputFile)

	if len(withoutText) > 0:
		print('### kein Text vorhanden ({0} MRs):'.format(len(withoutText)), file=outputFile)
		for m in withoutText:
			printMRInfo(*m, outputFile=outputFile)
			print('\n---', file=outputFile)

# close output file
if outputFile is not None:
	outputFile.close()
