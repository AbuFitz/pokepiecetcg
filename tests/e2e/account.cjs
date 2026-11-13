/* Account popup journeys (needs local fixture with registration enabled and the dev user from scripts/dev-wp.sh; wp-cli: option update woocommerce_enable_myaccount_registration yes). Local only. */
const { chromium, devices } = require('playwright');
const assert=require('node:assert/strict');let fail=0;const T=async(n,f)=>{try{await f();console.log('PASS',n)}catch(e){fail++;console.log('FAIL',n,e.message.split('\n')[0])}};
(async()=>{const b=await chromium.launch();const B=(process.env.BASE||'http://localhost:8088');
for(const [lab,opts] of [['desktop',{viewport:{width:1440,height:900}}],['mobile',{...devices['iPhone 13']}]]){
const ctx=await b.newContext(opts);await ctx.addCookies([{name:'pp_consent',value:encodeURIComponent(JSON.stringify({v:1,p:0,a:0,m:0,t:1})),url:(process.env.BASE||'http://localhost:8088')}]);const p=await ctx.newPage();const errs=[];p.on('pageerror',e=>errs.push(e.message));
await p.goto(B+'/',{waitUntil:'networkidle'});
const open=()=>p.evaluate(()=>document.querySelector(lab==='desktop'?'.pp-act-account':'.pp-menu-open').click());
await T(lab+': account icon opens popup (dialog, focus inside)',async()=>{if(lab==='mobile'){await p.evaluate(()=>document.querySelector('.pp-menu-open').click());await p.waitForSelector('#pp-menu.is-open');await p.evaluate(()=>document.querySelector('.pp-menu a[href*=my-account]').click())}else await p.evaluate(()=>document.querySelector('[data-pp-account]').click());await p.waitForSelector('#pp-account.is-open');assert.ok(await p.evaluate(()=>document.getElementById('pp-account').contains(document.activeElement)))});

await T(lab+': wrong password shows a generic error, stays on page',async()=>{await p.fill('#pp-in-user','nobody@example.com');await p.fill('#pp-in-pass','wrong-password');await p.click('#pp-pane-in button[type=submit]');await p.waitForFunction(()=>/didn’t work/.test(document.querySelector('#pp-pane-in .pp-form-msg').textContent));assert.ok(/pokepiece|localhost/.test(p.url()))});
await T(lab+': empty fields validated',async()=>{await p.fill('#pp-in-user','');await p.fill('#pp-in-pass','');await p.click('#pp-pane-in button[type=submit]');await p.waitForFunction(()=>/Enter your email/.test(document.querySelector('#pp-pane-in .pp-form-msg').textContent))});
await T(lab+': show/hide password toggles',async()=>{await p.click('#pp-pane-in .pp-pass__toggle');assert.equal(await p.getAttribute('#pp-in-pass','type'),'text')});
await T(lab+': valid sign in redirects to My account',async()=>{await p.fill('#pp-in-user','dev@example.invalid');await p.fill('#pp-in-pass','dev-only-local');await Promise.all([p.waitForURL(/my-account/),p.click('#pp-pane-in button[type=submit]')]);assert.ok(await p.locator('.woocommerce-MyAccount-navigation').count())});
await T(lab+': logged-in header link goes straight to account (no popup)',async()=>{await p.goto(B+'/',{waitUntil:'networkidle'});assert.equal(await p.locator('#pp-account').count(),0)});
await T(lab+': no console errors',async()=>assert.deepEqual(errs,[]));
await ctx.close();}
await b.close();process.exit(fail?1:0)})();
