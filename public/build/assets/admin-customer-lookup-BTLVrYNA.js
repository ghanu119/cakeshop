document.addEventListener("DOMContentLoaded",()=>{const s=document.querySelector("[data-customer-create-form]");if(!s)return;const u=s.dataset.lookupUrl,t=document.getElementById("customer-lookup-panel"),l=document.getElementById("customer-create-submit"),c=s.querySelectorAll("[data-lookup-field]");if(!t||!l)return;let i=null,a=t.dataset.serverRendered==="true";const p=(e,n,o)=>{t.classList.remove("hidden");let r=`<p class="font-semibold text-amber-900">${e}</p>`;if(n&&o){const v=n.email||"No email",L=o.email||"No email";r+=`
            <p class="mt-2 text-sm text-amber-800">
                Email: ${n.name} · ${v} · ${n.phone}
            </p>
            <p class="mt-1 text-sm text-amber-800">
                Phone: ${o.name} · ${L} · ${o.phone}
            </p>`}t.innerHTML=r,l.disabled=!0,a=!0},f=e=>{const n=e.email||"No email";t.classList.remove("hidden"),t.innerHTML=`
            <p class="font-semibold text-amber-900">Matching customer found</p>
            <p class="mt-1 text-sm text-amber-800">${e.name} · ${n} · ${e.phone}</p>
            <p class="mt-1 text-sm text-amber-800">${e.orders_count} orders · ${e.created_at}</p>
            <div class="mt-3 flex flex-wrap gap-3">
                <a href="${e.view_url}" class="text-sm font-medium text-indigo-700 hover:underline">View profile</a>
                <form method="post" action="${e.impersonate_url}" class="inline">
                    <input type="hidden" name="_token" value="${document.querySelector("meta[name=csrf-token]")?.content||""}">
                    <button type="submit" class="text-sm font-semibold text-indigo-700 hover:underline">Shop as customer</button>
                </form>
            </div>`,l.disabled=!0,a=!0},m=()=>{t.classList.add("hidden"),t.innerHTML="",t.removeAttribute("data-server-rendered"),l.disabled=!1,a=!1},d=()=>{const e=s.querySelector("#email")?.value?.trim()||"",n=s.querySelector("#phone")?.value?.trim()||"";if(!e&&!n){m();return}const o=new URLSearchParams;e&&o.set("email",e),n&&o.set("phone",n),fetch(`${u}?${o}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"}}).then(r=>{if(!r.ok)throw new Error("lookup failed");return r.json()}).then(r=>{if(t.removeAttribute("data-lookup-warning"),r.conflict){p(r.message,r.email_match,r.phone_match);return}if(r.match){f(r.match);return}m()}).catch(()=>{a||(t.classList.add("hidden"),t.innerHTML="",l.disabled=!1),t.dataset.lookupWarning="true"})};c.forEach(e=>{e.addEventListener("input",()=>{clearTimeout(i),i=setTimeout(d,400)})}),a&&(l.disabled=!0);const b=s.querySelector("#email")?.value?.trim()||"",h=s.querySelector("#phone")?.value?.trim()||"";(b||h)&&!a&&d()});
