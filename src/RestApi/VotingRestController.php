<?php

namespace Foodsharing\RestApi;

use Carbon\Carbon;
use DateTime;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;
use Foodsharing\Modules\Voting\DTO\Poll;
use Foodsharing\Modules\Voting\DTO\PollOption;
use Foodsharing\Modules\Voting\VotingGateway;
use Foodsharing\Modules\Voting\VotingTransactions;
use Foodsharing\Permissions\VotingPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class VotingRestController extends AbstractFOSRestController
{
	private Session $session;
	private VotingGateway $votingGateway;
	private VotingPermissions $votingPermissions;
	private VotingTransactions $votingTransactions;

	public function __construct(
		Session $session,
		VotingGateway $votingGateway,
		VotingPermissions $votingPermissions,
		VotingTransactions $votingTransactions)
	{
		$this->session = $session;
		$this->votingGateway = $votingGateway;
		$this->votingPermissions = $votingPermissions;
		$this->votingTransactions = $votingTransactions;
	}

	/**
	 * Returns the details of a poll.
	 *
	 * @OA\Parameter(name="pollId", in="path", @OA\Schema(type="integer"), description="which poll to return")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="404", description="Poll does not exist.")
	 * @OA\Tag(name="polls")
	 * @Rest\Get("polls/{pollId}", requirements={"pollId" = "\d+"})
	 */
	public function getPoll(int $pollId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		$poll = $this->votingTransactions->getPoll($pollId, true);
		if (is_null($poll)) {
			throw new NotFoundHttpException();
		}

		if (!$this->votingPermissions->maySeePoll($poll)) {
			throw new AccessDeniedHttpException();
		}

		return $this->handleView($this->view($poll, 200));
	}

	/**
	 * Lists all polls in a region or working group.
	 *
	 * @OA\Parameter(name="regionId", in="path", @OA\Schema(type="integer"), description="which region to list polls for")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Tag(name="polls")
	 * @Rest\Get("groups/{groupId}/polls", requirements={"groupId" = "\d+"})
	 */
	public function listPolls(int $groupId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		if (!$this->votingPermissions->mayListPolls($groupId)) {
			throw new AccessDeniedHttpException();
		}

		$polls = $this->votingGateway->listPolls($groupId);

		return $this->handleView($this->view($polls, 200));
	}

	/**
	 * Vote in a poll. The options need to be a list mapping option indices to the vote values (+1, 0, -1). Depending
	 * on the voting type not all options need to be included.
	 *
	 * @OA\Parameter(name="pollId", in="path", @OA\Schema(type="integer"), description="in which poll to vote")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="400", description="Invalid options.")
	 * @OA\Response(response="403", description="Insufficient permissions to vote in that polls.")
	 * @OA\Response(response="404", description="Poll does not exist.")
	 * @OA\Tag(name="polls")
	 * @Rest\Put("polls/{pollId}/vote", requirements={"pollId" = "\d+"})
	 * @Rest\RequestParam(name="options", nullable=false)
	 */
	public function voteAction(int $pollId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		// check if poll exists and user may vote
		$poll = $this->votingGateway->getPoll($pollId, false);
		if (is_null($poll)) {
			throw new NotFoundHttpException();
		}

		if (!$this->votingPermissions->mayVote($poll)) {
			throw new AccessDeniedHttpException();
		}

		// convert option indices to integers to avoid type problems
		$options = $paramFetcher->get('options');
		$options = array_combine(array_map('intval', array_keys($options)),
			array_map('intval', array_values($options)));

		// check if voting options are valid
		if (!$this->votingTransactions->vote($poll, $options)) {
			throw new BadRequestHttpException();
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Creates a new poll. The poll and all its options will be assigned valid IDs and option indices by the
	 * server. Options must be passed as an array of strings for the options' texts. The order of the options will
	 * be kept.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="400", description="Invalid parameters.")
	 * @OA\Response(response="403", description="Insufficient permissions to create a poll in that region.")
	 * @OA\Tag(name="polls")
	 * @Rest\Post("polls")
	 * @Rest\RequestParam(name="name", nullable=false)
	 * @Rest\RequestParam(name="description", nullable=false)
	 * @Rest\RequestParam(name="startDate", nullable=false)
	 * @Rest\RequestParam(name="endDate", nullable=false)
	 * @Rest\RequestParam(name="regionId", nullable=false, requirements="\d+")
	 * @Rest\RequestParam(name="scope", nullable=false, requirements="\d+")
	 * @Rest\RequestParam(name="type", nullable=false, requirements="\d+")
	 * @Rest\RequestParam(name="options", nullable=false)
	 * @Rest\RequestParam(name="notifyVoters", nullable=false)
	 * @Rest\RequestParam(name="shuffleOptions", default=true)
	 */
	public function createPollAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}

		// parse and check parameters
		$poll = new Poll();
		$poll->name = trim($paramFetcher->get('name'));
		$poll->description = trim($paramFetcher->get('description'));
		if (empty($poll->name) || empty($poll->description)) {
			throw new BadRequestHttpException('empty name or description: ' . $poll->name . ', ' . $poll->description);
		}

		$poll->startDate = DateTime::createFromFormat(DateTime::ISO8601, $paramFetcher->get('startDate'));
		$poll->endDate = DateTime::createFromFormat(DateTime::ISO8601, $paramFetcher->get('endDate'));
		if (!$poll->startDate || !$poll->endDate || $poll->startDate >= $poll->endDate
		|| Carbon::now()->add($this->votingPermissions->editTimeAfterPollCreation()) >= $poll->startDate) {
			throw new BadRequestHttpException('invalid start or end date');
		}

		$poll->scope = (int)$paramFetcher->get('scope');
		if (!VotingScope::isValidScope($poll->scope)) {
			throw new BadRequestHttpException('invalid scope');
		}
		$poll->type = (int)$paramFetcher->get('type');
		if (!VotingType::isValidType($poll->type)) {
			throw new BadRequestHttpException('invalid poll type');
		}

		$poll->regionId = (int)$paramFetcher->get('regionId');
		if (!$this->votingPermissions->mayCreatePoll($poll->regionId)) {
			throw new AccessDeniedHttpException();
		}

		$poll->authorId = $this->session->id();

		// parse options and check that they are not empty
		$poll->options = $this->parseOptions($paramFetcher->get('options'));
		$poll->shuffleOptions = boolval($paramFetcher->get('shuffleOptions'));

		// create poll
		$this->votingTransactions->createPoll($poll, $paramFetcher->get('notifyVoters'));

		return $this->handleView($this->view($poll, 200));
	}

	/**
	 * Updates an existing poll. This can change a poll's title, description, and options. Updating
	 * is only possible within one hour after creation.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="400", description="Invalid parameters")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions to edit that poll")
	 * @OA\Response(response="404", description="Poll does not exist")
	 * @OA\Tag(name="polls")
	 * @Rest\Patch("polls/{pollId}", requirements={"pollId" = "\d+"})
	 * @Rest\RequestParam(name="name", nullable=true, default=null)
	 * @Rest\RequestParam(name="description", nullable=true, default=null)
	 * @Rest\RequestParam(name="options", nullable=true, default=null)
	 */
	public function editPollAction(int $pollId, ParamFetcher $paramFetcher): Response
	{
		// check permissions and get poll
		$userId = $this->session->id();
		if (!$userId) {
			throw new UnauthorizedHttpException('');
		}

		$poll = $this->votingGateway->getPoll($pollId, false);
		if (is_null($poll)) {
			throw new NotFoundHttpException();
		}

		if (!$this->votingPermissions->mayEditPoll($poll)) {
			throw new AccessDeniedHttpException();
		}

		// check name and description
		$name = $paramFetcher->get('name');
		if (!empty($name)) {
			$poll->name = trim($name);
		}
		$description = trim($paramFetcher->get('description'));
		if (!empty($description)) {
			$poll->description = $description;
		}

		// parse options and check that they are not empty
		$options = $paramFetcher->get('options');
		if (!empty($options)) {
			$poll->options = $this->parseOptions($options);
		}

		// update poll
		$this->votingTransactions->updatePoll($poll);

		return $this->handleView($this->view($poll, 200));
	}

	/**
	 * Parses poll options from a request and returns them as {@see PollOption} objects. Throws exceptions if
	 * the list is empty or if any option does not have a valid text.
	 */
	private function parseOptions(array $data): array
	{
		$options = array_map(function ($x) {
			$o = new PollOption();
			$o->text = trim($x);

			return $o;
		}, $data);
		if (empty($options)) {
			throw new BadRequestHttpException('poll does not have any options');
		}

		// check that no option text is empty
		foreach ($options as $option) {
			if (empty($option->text)) {
				throw new BadRequestHttpException('option text must not be empty');
			}
		}

		// check that no two option texts are equal
		$texts = array_map(function ($o) {
			return $o->text;
		}, $options);
		if (sizeof(array_unique($texts)) != sizeof($texts)) {
			throw new BadRequestHttpException('poll options must not have the same text');
		}

		return $options;
	}

	/**
	 * Deletes a poll.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="403", description="Insufficient permissions to delete that poll.")
	 * @OA\Response(response="404", description="Poll does not exist.")
	 * @OA\Tag(name="polls")
	 * @Rest\Delete("polls/{pollId}", requirements={"pollId" = "\d+"})
	 */
	public function deletePollAction(int $pollId): Response
	{
		if (!$this->session->id()) {
			throw new UnauthorizedHttpException('');
		}
		$poll = $this->votingGateway->getPoll($pollId, false);
		if (is_null($poll)) {
			throw new NotFoundHttpException();
		}

		if (!$this->votingPermissions->mayEditPoll($poll)) {
			throw new AccessDeniedHttpException();
		}

		$this->votingTransactions->deletePoll($pollId);

		return $this->handleView($this->view([], 200));
	}
}
