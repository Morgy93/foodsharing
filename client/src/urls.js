// these are used for generating link-paths inside vue
// e.g. $url('profile', 15)

const urls = {
  profile: (id) => `/profile/${id}`,
  forum: (regionId, subforumId = 0, threadId = null, postId = null, newThread = false) => {
    return (`/?page=bezirk&bid=${regionId}` +
      `&sub=${(subforumId === 1) ? 'botforum' : 'forum'}` +
      (threadId === null ? '' : `&tid=${threadId}`) +
      (postId === null ? '' : `&pid=${postId}#post-${postId}`) +
      (newThread ? '&newthread=1' : '')
    )
  },
  academy: () => '/?page=content&sub=academy',
  basket: (basketId) => `/essenskoerbe/${basketId}`,
  baskets: () => '/essenskoerbe',
  blog: () => '/news',
  blogAdd: () => '/?page=blog&sub=add',
  blogEdit: (blogId) => `/?page=blog&sub=edit&id=${blogId}`,
  blogList: () => '/?page=blog&sub=manage',
  calendarHints: () => 'https://wiki.foodsharing.de/Kalenderexport',
  changelog: () => '/?page=content&sub=changelog',
  claims: () => '/?page=content&sub=forderungen',
  communitiesAustria: () => '/?page=content&sub=communitiesAustria',
  communitiesGermany: () => '/?page=content&sub=communitiesGermany',
  communitiesSwitzerland: () => '/?page=content&sub=communitiesSwitzerland',
  contact: () => '/?page=content&sub=contact',
  contentEdit: () => '/?page=content',
  conversations: (conversationId = null) => `/?page=msg${conversationId ? `&cid=${conversationId}` : ''}`,
  dashboard: () => '/?page=dashboard',
  dataprivacy: () => '/?page=legal',
  donate: () => '/unterstuetzung',
  email: () => '/?page=email',
  event: (eventId) => `/?page=event&id=${eventId}`,
  eventEdit: (eventId) => `/?page=event&id=${eventId}&sub=edit`,
  events: (regionId) => `/?page=bezirk&bid=${regionId}&sub=events`,
  support: () => 'https://foodsharing.freshdesk.com/support/home',
  festival: () => '/?page=content&sub=festival',
  foodsaverList: (regionId) => `/?page=foodsaver&bid=${regionId}`,
  foodsharepoint: (fspId) => `/?page=fairteiler&sub=ft&id=${fspId}`,
  foodsharepoints: (regionId) => `/?page=bezirk&bid=${regionId}&sub=fairteiler`,
  fsstaedte: () => '/?page=content&sub=fsstaedte',
  grundsaetze: () => 'https://wiki.foodsharing.de/Grundsätze',
  guide: () => 'https://wiki.foodsharing.de/Hygiene-Ratgeber_f%C3%BCr_Lebensmittel',
  guideLockedEmail: () => 'https://foodsharing.freshdesk.com/support/solutions/articles/77000299947-e-mail-sperre-im-profil',
  home: () => '/',
  imprint: () => '/impressum',
  infos: () => '/?page=content&sub=infohub',
  infosCompany: () => '/fuer-unternehmen',
  international: () => '/?page=content&sub=international',
  joininfo: () => '/?page=content&sub=joininfo',
  leeretonne: () => '/?page=content&sub=leeretonne',
  legal_agreement: () => 'https://wiki.foodsharing.de/Rechtsvereinbarung',
  login: () => '/?page=login',
  logout: () => '/?page=logout',
  mailbox: (mailboxId = null) => `/?page=mailbox${mailboxId ? `&show=${mailboxId}` : ''}`,
  mailboxManage: () => '/?page=mailbox&a=manage',
  mailboxMailto: (email) => `/?page=mailbox&mailto=${email}`,
  map: () => '/karte',
  members: (regionId) => `/?page=bezirk&bid=${regionId}&sub=members`,
  mission: () => '/ueber-uns',
  options: (regionId) => `/?page=bezirk&bid=${regionId}&sub=options`,
  partner: () => '/partner',
  passports: (regionId) => `/?page=passgen&bid=${regionId}`,
  passwordReset: () => '/?page=login&sub=passwordReset',
  pin: (regionId) => `/?page=bezirk&bid=${regionId}&sub=pin`,
  poll: (pollId) => `/?page=poll&id=${pollId}`,
  pollEdit: (pollId) => `/?page=poll&id=${pollId}&sub=edit`,
  pollNew: (regionId) => `/?page=poll&bid=${regionId}&sub=new`,
  polls: (regionId) => `/?page=bezirk&bid=${regionId}&sub=polls`,
  press: () => '/?page=content&sub=presse',
  region: () => '/?page=region',
  releaseNotes: () => '/?page=content&sub=releaseNotes',
  reports: (regionId = null) => regionId ? `/?page=report&bid=${regionId}` : '/?page=report',
  settings: () => '/?page=settings',
  settingsCalendar: () => '/?page=settings&sub=calendar',
  settingsNotifications: () => '/?page=settings&sub=info',
  statistic: (regionId) => `/?page=bezirk&bid=${regionId}&sub=statistic`,
  statistics: () => '/statistik',
  store: (storeId) => `/?page=fsbetrieb&id=${storeId}`,
  storeAdd: (regionId = null) => regionId ? `/?page=betrieb&a=new&bid=${regionId}` : '/?page=betrieb&a=new',
  storeList: () => '/?page=fsbetrieb',
  stores: (regionId) => `/?page=betrieb&bid=${regionId}`,
  storeEdit: (storeId) => `/?page=betrieb&a=edit&id=${storeId}`,
  team: () => '/team',
  transparency: () => '/?page=content&sub=transparency',
  wall: (regionId) => `/?page=bezirk&bid=${regionId}&sub=wall`,
  wiki: () => 'https://wiki.foodsharing.de/',
  wiki_voting: () => 'https://wiki.foodsharing.de/Abstimmungs-Modul',
  workingGroupEdit: (groupId) => `/?page=groups&sub=edit&id=${groupId}`,
  workingGroups: (regionId = null) => regionId ? `/?page=groups&p=${regionId}` : '/?page=groups',
  workshops: () => '/?page=content&sub=workshops',
  urlencode: (url) => encodeURIComponent(`${url}`),
  donations: () => 'https://spenden.foodsharing.de',
  circle_of_friends: () => 'https://spenden.foodsharing.de/freundeskreis',
  selfservice: () => 'https://spenden.foodsharing.de/selfservice',
  resendActivationMail: () => '/?page=login&a=resendActivationMail',

  //
  quizEdit: () => '/?page=quiz',
  quizLearning: () => 'https://youtu.be/9Fk6MHC-M1o',
  quizFs: () => '/?page=settings&sub=up_fs',
  quizBip: () => '/?page=settings&sub=up_bip',
  quizBot: () => '/?page=settings&sub=up_bot',

  // Footer Links
  hosting: () => 'https://manitu.de',
  wiener_tafel: () => 'https://www.wienertafel.at',
  bmlfuw: () => 'https://www.bmlrt.gv.at',
  denns: () => 'https://www.denns-biomarkt.at',
  devdocs: () => 'https://devdocs.foodsharing.network',
  devdocsItTasks: () => 'https://devdocs.foodsharing.network/it-tasks.html',

  // Beta Testing
  beta: () => 'https://beta.foodsharing.de',
  beta_testing_forum: () => 'https://beta.foodsharing.de/?page=bezirk&bid=734&sub=forum',

  // Gitlab
  git_revision: (revision) => `https://gitlab.com/foodsharing-dev/foodsharing/tree/${revision}`,

  // Social Media
  twitter_de: () => 'https://twitter.com/FoodsharingDE',
  twitter_at: () => 'https://twitter.com/FoodsharingDE', // GERMAN VERSION
  linkedin_de: () => 'https://www.linkedin.com/company/foodsharingde',
  linkedin_at: () => 'https://www.linkedin.com/company/foodsharingde', // GERMAN VERSION
  youtube_de: () => 'https://www.youtube.com/user/foodsharingtv',
  youtube_at: () => 'https://www.youtube.com/user/foodsharingtv', // GERMAN VERSION
  instagram_de: () => 'https://www.instagram.com/foodsharing_de',
  instagram_at: () => 'https://instagram.com/foodsharing.at',
  facebook_de: () => 'https://www.facebook.com/foodsharing.de',
  facebook_at: () => 'https://www.facebook.com/oesterreichfoodsharing',
  tiktok_de: () => 'https://www.tiktok.com/@foodsharing.de',
  tiktok_at: () => 'https://www.tiktok.com/@foodsharing.de', // GERMAN VERSION
}

const url = (key, ...params) => {
  if (!urls[key]) {
    console.error(new Error(`url() Error: url key '${key}' does not exist`))
    return '#'
  }
  return urls[key](...params)
}

export { url, urls }
