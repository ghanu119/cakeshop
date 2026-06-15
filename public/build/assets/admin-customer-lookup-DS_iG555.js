document.addEventListener("DOMContentLoaded",()=>{const s=document.querySelector("[data-customer-create-form]");if(!s)return;const l=s.dataset.lookupUrl,t=document.getElementById("customer-lookup-panel"),o=document.getElementById("customer-create-submit"),c=s.querySelectorAll("[data-lookup-field]");let a=null;const m=()=>{const n=s.querySelector("#email")?.value?.trim()||"",r=s.querySelector("#phone")?.value?.trim()||"";if(!n&&!r){t.classList.add("hidden"),o.disabled=!1;return}const i=new URLSearchParams;n&&i.set("email",n),r&&i.set("phone",r),fetch(`${l}?${i}`,{headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"}}).then(e=>e.json()).then(e=>{if(e.conflict){t.classList.remove("hidden"),t.innerHTML=`<p class="font-semibold text-amber-900">${e.message}</p>`,o.disabled=!0;return}if(e.match){const d=e.match.email||"No email";t.classList.remove("hidden"),t.innerHTML=`
                        <p class="font-semibold text-amber-900">Matching customer found</p>
                        <p class="mt-1 text-sm text-amber-800">${e.match.name} · ${d} · ${e.match.phone}</p>
                        <p class="mt-1 text-sm text-amber-800">${e.match.orders_count} orders · ${e.match.created_at}</p>
                        <div class="mt-3 flex flex-wrap gap-3">
                            <a href="${e.match.view_url}" class="text-sm font-medium text-indigo-700 hover:underline">View profile</a>
                            <form method="post" action="${e.match.impersonate_url}" class="inline">
                                <input type="hidden" name="_token" value="${document.querySelector("meta[name=csrf-token]")?.content||""}">
                                <button type="submit" class="text-sm font-semibold text-indigo-700 hover:underline">Shop as customer</button>
                            </form>
                        </div>`,o.disabled=!0;return}t.classList.add("hidden"),o.disabled=!1}).catch(()=>{t.classList.add("hidden"),o.disabled=!1})};c.forEach(n=>{n.addEventListener("input",()=>{clearTimeout(a),a=setTimeout(m,400)})})});
