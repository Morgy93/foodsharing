<?php

namespace Foodsharing\RestApi;

use Carbon\Carbon;
use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Gender;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverTransactions;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Modules\Profile\ProfileTransactions;
use Foodsharing\Modules\Register\DTO\RegisterData;
use Foodsharing\Modules\Register\RegisterTransactions;
use Foodsharing\Modules\Uploads\UploadsGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Permissions\UserPermissions;
use Foodsharing\Utility\EmailHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Mobile_Detect;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserRestController extends AbstractFOSRestController
{
	private Session $session;
	private LoginGateway $loginGateway;
	private FoodsaverGateway $foodsaverGateway;
	private ProfileGateway $profileGateway;
	private UploadsGateway $uploadsGateway;
	private ReportPermissions $reportPermissions;
	private UserPermissions $userPermissions;
	private ProfilePermissions $profilePermissions;
	private EmailHelper $emailHelper;
	private RegisterTransactions $registerTransactions;
	private ProfileTransactions $profileTransactions;
	private FoodsaverTransactions $foodsaverTransactions;

	private const MIN_RATING_MESSAGE_LENGTH = 100;
	private const MIN_PASSWORD_LENGTH = 8;
	private const MIN_AGE_YEARS = 18;

	public function __construct(
		Session $session,
		LoginGateway $loginGateway,
		FoodsaverGateway $foodsaverGateway,
		ProfileGateway $profileGateway,
		UploadsGateway $uploadsGateway,
		ReportPermissions $reportPermissions,
		UserPermissions $userPermissions,
		ProfilePermissions $profilePermissions,
		EmailHelper $emailHelper,
		RegisterTransactions $registerTransactions,
		ProfileTransactions $profileTransactions,
		FoodsaverTransactions $foodsaverTransactions
	) {
		$this->session = $session;
		$this->loginGateway = $loginGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profileGateway = $profileGateway;
		$this->uploadsGateway = $uploadsGateway;
		$this->reportPermissions = $reportPermissions;
		$this->userPermissions = $userPermissions;
		$this->profilePermissions = $profilePermissions;
		$this->emailHelper = $emailHelper;
		$this->registerTransactions = $registerTransactions;
		$this->profileTransactions = $profileTransactions;
		$this->foodsaverTransactions = $foodsaverTransactions;
	}

	/**
	 * Checks if the user is logged in and lists the basic user information. Returns 200 and the user data, 404 if the
	 * user does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("user/{id}", requirements={"id" = "\d+"})
	 */
	public function userAction(int $id): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$data = $this->foodsaverGateway->getFoodsaverBasics($id);
		if (!$data || empty($data)) {
			throw new HttpException(404, 'User does not exist.');
		}

		return $this->handleView($this->view(RestNormalization::normalizeUser($data), 200));
	}

	/**
	 * Checks if the user is logged in  and lists the basic user information. Returns 401 if not logged in or 200 and
	 * the user data.
	 *
	 * @Rest\Get("user/current")
	 */
	public function currentUserAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		return $this->userAction($this->session->id());
	}

	/**
	 * Lists the detailed profile of a user. Returns 403 if not allowed or 200 and the data.
	 *
	 * @Rest\Get("user/{id}/details", requirements={"id" = "\d+"})
	 */
	public function userDetailsAction(int $id): Response
	{
		if (!$this->userPermissions->maySeeUserDetails($id)) {
			throw new HttpException(403);
		}

		$data = $this->profileGateway->getData($id, -1, $this->reportPermissions->mayHandleReports());
		if (!$data || empty($data)) {
			throw new HttpException(404, 'User does not exist.');
		}

		return $this->handleView($this->view(RestNormalization::normaliseUserDetails($data), 200));
	}

	/**
	 * Lists the detailed profile of the current user. Returns 401 if not logged in or 200 and the data.
	 *
	 * @Rest\Get("user/current/details")
	 */
	public function currentUserDetailsAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		return $this->userDetailsAction($this->session->id());
	}

	/**
	 * @Rest\Post("user/login")
	 * @Rest\RequestParam(name="email")
	 * @Rest\RequestParam(name="password")
	 * @Rest\RequestParam(name="remember_me", default=false)
	 */
	public function loginAction(ParamFetcher $paramFetcher): Response
	{
		$email = $paramFetcher->get('email');
		$password = $paramFetcher->get('password');
		$rememberMe = (bool)$paramFetcher->get('remember_me');
		$fs_id = $this->loginGateway->login($email, $password);
		if ($fs_id) {
			$this->session->login($fs_id, $rememberMe);

			$mobdet = new Mobile_Detect();
			if ($mobdet->isMobile()) {
				$_SESSION['mob'] = 1;
			}

			// retrieve user data and normalise it
			$user = $this->foodsaverGateway->getFoodsaverBasics($fs_id);
			if (!$user || empty($user)) {
				throw new HttpException(404, 'User does not exist.');
			}
			$normalizedUser = RestNormalization::normalizeUser($user);

			return $this->handleView($this->view($normalizedUser, 200));
		}

		throw new HttpException(401, 'email or password are invalid');
	}

	/**
	 * @Rest\Post("user/logout")
	 */
	public function logoutAction(): Response
	{
		$this->session->logout();

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Tests if an email address is valid for registration. Returns 400 if the parameter is not an email address or 200
	 * and a 'valid' parameter that indicates if the email address can be used for registration.
	 *
	 * @Rest\Post("user/isvalidemail")
	 * @Rest\RequestParam(name="email", nullable=false)
	 */
	public function testRegisterEmailAction(ParamFetcher $paramFetcher): Response
	{
		$email = $paramFetcher->get('email');
		if (empty($email) || !$this->emailHelper->validEmail($email)) {
			throw new HttpException(400, 'email is not valid');
		}

		return $this->handleView($this->view([
			'valid' => $this->isEmailValidForRegistration($email)
		], 200));
	}

	/**
	 * Registers a new user.
	 *
	 * @Rest\Post("user")
	 * @Rest\RequestParam(name="firstname", nullable=false)
	 * @Rest\RequestParam(name="lastname", nullable=false)
	 * @Rest\RequestParam(name="email", nullable=false)
	 * @Rest\RequestParam(name="password", nullable=false)
	 * @Rest\RequestParam(name="gender", nullable=false, requirements="\d+")
	 * @Rest\RequestParam(name="birthdate", nullable=false)
	 * @Rest\RequestParam(name="mobilePhone", nullable=true)
	 * @Rest\RequestParam(name="subscribeNewsletter", requirements="(0|1)", default=0)
	 */
	public function registerUserAction(ParamFetcher $paramFetcher): Response
	{
		// validate data
		$data = new RegisterData();
		$data->firstName = trim(strip_tags($paramFetcher->get('firstname')));
		$data->lastName = trim(strip_tags($paramFetcher->get('lastname')));
		if (empty($data->firstName) || empty($data->lastName)) {
			throw new HttpException(400, 'names must not be empty');
		}

		$data->email = trim($paramFetcher->get('email'));
		if (empty($data->email) || !$this->emailHelper->validEmail($data->email)
			|| !$this->isEmailValidForRegistration($data->email)) {
			throw new HttpException(400, 'email is not valid or already used');
		}

		$data->password = trim($paramFetcher->get('password'));
		if (strlen($data->password) < self::MIN_PASSWORD_LENGTH) {
			throw new HttpException(400, 'password is too short');
		}

		$data->gender = (int)$paramFetcher->get('gender');
		if (!Gender::isValid($data->gender)) {
			$data->gender = Gender::NOT_SELECTED;
		}

		$birthdate = Carbon::createFromFormat('Y-m-d', $paramFetcher->get('birthdate'));
		if (empty($birthdate)) {
			throw new HttpException(400, 'invalid birthdate');
		}
		$minBirthdate = Carbon::today()->subYears(self::MIN_AGE_YEARS);
		if ($birthdate > $minBirthdate) {
			throw new HttpException(400, 'you are not old enough');
		}
		$data->birthday = $birthdate;

		$data->mobilePhone = strip_tags($paramFetcher->get('mobilePhone') ?? '');
		$data->subscribeNewsletter = (int)$paramFetcher->get('subscribeNewsletter') == 1;

		try {
			// register user and send out registration email
			$id = $this->registerTransactions->registerUser($data);

			// return the created user
			$user = RestNormalization::normalizeUser($this->foodsaverGateway->getFoodsaverBasics($id));

			return $this->handleView($this->view($user, 200));
		} catch (\Exception $e) {
			throw new HttpException(500, 'could not register user');
		}
	}

	private function isEmailValidForRegistration(string $email): bool
	{
		return !$this->emailHelper->isFoodsharingEmailAddress($email)
			&& !$this->foodsaverGateway->emailExists($email);
	}

	/**
	 * @Rest\Delete("user/{userId}", requirements={"userId" = "\d+"})
	 */
	public function deleteUserAction(int $userId): Response
	{
		if (!$this->profilePermissions->mayDeleteUser($userId)) {
			throw new HttpException(403);
		}

		// needs the session ID, so we can't log out just yet
		$this->foodsaverTransactions->deleteFoodsaver($userId);

		if ($userId === $this->session->id()) {
			$this->session->logout();
		}

		return $this->handleView($this->view());
	}

	/**
	 * Gives a banana to a user.
	 *
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="to which user to give the banana")
	 * @OA\RequestBody(description="message to the user")
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="400", description="Accompanying message is too short.")
	 * @OA\Response(response="403", description="Insufficient permissions to rate that user.")
	 * @OA\Response(response="404", description="User to rate does not exist.")
	 * @OA\Tag(name="user")
	 *
	 * @Rest\Put("user/{userId}/banana", requirements={"userId" = "\d+"})
	 * @Rest\RequestParam(name="message", nullable=false)
	 */
	public function addBanana(int $userId, ParamFetcher $paramFetcher): Response
	{
		// make sure that users may not give themselves bananas
		if (!$this->session->may() || $this->session->id() === $userId) {
			throw new HttpException(403);
		}

		// check if the user exists
		if (!$this->foodsaverGateway->foodsaverExists($userId)) {
			throw new HttpException(404);
		}

		// do not allow giving bananas twice
		if ($this->profileGateway->hasGivenBanana($this->session->id(), $userId)) {
			throw new HttpException(403);
		}

		// check length of message
		$message = trim($paramFetcher->get('message'));
		if (strlen($message) < self::MIN_RATING_MESSAGE_LENGTH) {
			throw new HttpException(400, 'text too short: ' . strlen($message) . ' < ' . self::MIN_RATING_MESSAGE_LENGTH);
		}

		$this->profileTransactions->giveBanana($userId, $message, $this->session->id());

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Deletes a banana.
	 *
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="the owner of the banana")
	 * @OA\Parameter(name="senderId", in="path", @OA\Schema(type="integer"), description="the sender of the banana")
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="401", description="Not logged in.")
	 * @OA\Response(response="403", description="Insufficient permissions to delete that banana.")
	 * @OA\Response(response="404", description="Banana does not exist.")
	 * @OA\Tag(name="user")
	 *
	 * @Rest\Delete("user/{userId}/banana/{senderId}", requirements={"userId" = "\d+"})
	 */
	public function deleteBanana(int $userId, int $senderId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		if (!$this->profilePermissions->mayDeleteBanana($userId)) {
			throw new HttpException(403);
		}

		$isDeleted = $this->profileGateway->removeBanana($userId, $senderId);

		return $this->handleView($this->view([], $isDeleted ? 200 : 404));
	}

	/**
	 * Sets a previously uploaded picture as the user's profile photo.
	 *
	 * @OA\RequestBody(description="UUID of the previously uploaded file")
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="400", description="File does not exist.")
	 * @OA\Response(response="401", description="Not logged in.")
	 * @OA\Response(response="403", description="File was not uploaded by this user.")
	 * @OA\Tag(name="user")
	 *
	 * @Rest\Patch("user/photo")
	 * @Rest\RequestParam(name="uuid", nullable=false)
	 */
	public function setProfilePictureAction(ParamFetcher $paramFetcher): Response
	{
		$userId = $this->session->id();
		if (!$userId) {
			throw new HttpException(401);
		}

		// check if the photo exists and was uploaded by this user
		$uuid = trim($paramFetcher->get('uuid'));
		try {
			if ($this->uploadsGateway->getUser($uuid) !== $userId) {
				throw new HttpException(403);
			}
		} catch (Exception $e) {
			throw new HttpException(400);
		}

		$this->foodsaverGateway->updatePhoto($this->session->id(), '/api/uploads/' . $uuid);
		$this->session->refreshFromDatabase();

		return $this->handleView($this->view([], 200));
	}

	private function handleUserView(): Response
	{
		$user = RestNormalization::normalizeUser([
			'id' => $this->session->id(),
			'name' => $this->session->get('user')['name']
		]);

		return $this->handleView($this->view($user, 200));
	}

	/**
	 * Removes the user from the email bounce list. This will have no effect and return 200 if the user was
	 * not on the bounce list.
	 *
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="which user to remove from the list")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="403", description="Insufficient permissions")
	 * @OA\Tag(name="user")
	 *
	 * @Rest\Delete("user/{userId}/emailbounce", requirements={"userId" = "\d+"})
	 */
	public function removeFromBounceListAction(int $userId): Response
	{
		if (!$this->session->may() || !$this->profilePermissions->mayRemoveFromBounceList($userId)) {
			throw new HttpException(403);
		}

		$this->profileTransactions->removeUserFromBounceList($userId);

		return $this->handleView($this->view([], 200));
	}
}
