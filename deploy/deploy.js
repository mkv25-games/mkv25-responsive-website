const ftpDeploy = require('ftp-deploy')
const fs = require('fs')
const path = require('path')

const passwords = JSON.parse(fs.readFileSync(path.join(__dirname, '.ftppass')))
const mode = process.argv[2] || false

const FTP_HOST = 'ftp.mkv25.net'
const FTP_USER_STAGE = 'mkv25-stage'
const FTP_USER_LIVE = 'mkv25-live'
const FTP_ROOT_PATH = ''

const stageUser = passwords[FTP_USER_STAGE]
const liveUser = passwords[FTP_USER_LIVE]

const defaultConfig = {
  user: stageUser.username,
  password: stageUser.password, // optional, prompted if none given
  host: FTP_HOST,
  port: 21,
  localRoot: path.join(__dirname, '../site'),
  remoteRoot: '/site',
  // include: ['*', '**/*'],      // this would upload everything except dot files
  include: [], // ['*.php', 'dist/*'],
  exclude: ['.ftp*', '.git*', '.hta*', 'deploy', '*.fdproj', 'tasklist.md', 'readme.md', '.sublime', 'secret.config.php'], // ['dist/**/*.map'], // e.g. exclude sourcemaps - ** exclude: [] if nothing to exclude **
  deleteRemote: false, // delete existing files at destination before uploading
  forcePasv: true // Passive mode is forced (EPSV command is not sent)
}

const modes = {
  'stage-images': () => deploy({
    include: ['images/**/*']
  }),
  'stage-code': () => runModes(['stage-php', 'stage-scripts', 'stage-stylesheets', 'stage-templates']),
  'stage-all': () => runModes(['stage-code', 'stage-content', 'stage-articles', 'stage-projects', 'stage-images']),
  'stage-php': () => deploy({
    include: ['php/**/*'],
    exclude: ['php/lib/external/**/*']
  }),
  'stage-php-lib': () => deploy({
    include: ['php/lib/external/**/*']
  }),
  'stage-scripts': () => deploy({
    include: ['scripts/**/*']
  }),
  'stage-stylesheets': () => deploy({
    include: ['stylesheets/**/*']
  }),
  'stage-templates': () => deploy({
    include: ['templates/**/*']
  }),
  'stage-content': () => deploy({
    include: ['content/**/*']
  }),
  'stage-articles': () => deploy({
    include: ['articles/**/*']
  }),
  'stage-projects': () => deploy({
    include: ['projects/**/*']
  }),
  'live-images': () => deploy({
    include: ['images/**/*']
  }, FTP_USER_LIVE),
  'live-php': () => deploy({
    include: ['php/**/*'],
    exclude: ['php/lib/external/**/*']
  }, FTP_USER_LIVE),
  'live-content': () => deploy({
    include: ['content/**/*']
  }, FTP_USER_LIVE),
  'live-articles': () => deploy({
    include: ['articles/**/*']
  }, FTP_USER_LIVE),
  'live-release': () => deploy({
    include: ['**/*']
  }, FTP_USER_LIVE)
}

function createDeployer() {
  const deployer = new ftpDeploy()
  deployer.on('uploading', function(data) {
    console.log('[Deploy] Uploading :', `${data.transferredFileCount} / ${data.totalFilesCount} ::: ${data.filename}`)
  })

  deployer.on('uploaded', function(data) {
    console.log('[Deploy] Uploaded  :', `${data.transferredFileCount} / ${data.totalFilesCount} ::: ${data.filename}`)
  })

  deployer.on('log', function(data) {
    console.log('[Deploy]', data)
  })

  return deployer
}

async function deploy(variation, passwordId) {
  try {
    console.log('[Deploy]', mode, ':', variation)

    // add password after logging the variation
    const user = passwords[passwordId] || stageUser
    variation.user = user.username
    variation.password = user.password

    // create and configure deployer
    const deployer = createDeployer()
    const ftpConfig = Object.assign({}, defaultConfig, variation)
    ftpConfig.exclude = [].concat(defaultConfig.exclude, variation.exclude || [])

    const result = await deployer.deploy(ftpConfig)

    console.log('[Deploy] Finished:', result)
  }
  catch (ex) {
    console.log('[Deploy] Error:', ex)
  }
}

async function runModes(modes) {
  const results = []
  while(modes.length > 0) {
    const mode = modes.shift()
    let result = await run(mode)
    results.push(results)
  }
  return results
}

async function run(mode) {
  console.log('[Deploy]', mode)
  const fn = modes[mode]
  if (fn) {
    await fn()
    console.log('[Deploy] Completed', mode)
  } else {
    console.log(`[Deploy] No mode found with the name: (${mode})`)
    console.log('  Available modes:', Object.keys(modes).join(', '))
  }
}

run(mode)
