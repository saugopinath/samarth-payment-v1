            <!-- Dark Footer (Full-Width, touching the bottom of screen) -->
            <footer x-data="footerModals()" class="text-white text-center py-6 px-4 md:px-12 border-t border-slate-800 w-full mt-auto" style="background-color: #0f172a;">
                 <p class="text-xs md:text-sm text-slate-200 font-medium max-w-5xl mx-auto leading-relaxed">
                     This site is designed by <span class="font-bold">National Informatics Centre (NIC)</span>. Content, DATA, Process and Operation owned and maintained by <span class="font-bold">Finance Department , Government of West Bengal</span>.
                 </p>
                 <p class="text-[10px] md:text-xs text-slate-300 mt-2.5 tracking-wider font-display">
                     Best Viewed in Google Chrome | 
                     <a href="#" @click.prevent="openModal('Legal Disclaimer')" class="text-amber-400 hover:text-amber-300 hover:underline">Legal Disclaimer</a> | 
                     <a href="#" @click.prevent="openModal('Copyright Policy')" class="text-amber-400 hover:text-amber-300 hover:underline">Copyright Policy</a> | 
                     <a href="#" @click.prevent="openModal('Privacy Policy')" class="text-amber-400 hover:text-amber-300 hover:underline">Privacy Policy</a> | 
                     <a href="#" @click.prevent="openModal('Hyperlink Policy')" class="text-amber-400 hover:text-amber-300 hover:underline">Hyperlink Policy</a> | 
                     <a href="{{ route('terms') }}" class="text-amber-400 hover:text-amber-300 hover:underline">Terms & Condition</a>
                 </p>

                <!-- AlpineJS Modal -->
                <div x-show="isOpen" style="display: none;" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 sm:p-6 text-left">
                    <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal()"></div>
                    
                    <div x-show="isOpen" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full flex flex-col overflow-hidden border border-slate-200 min-h-0"
                         style="max-height: calc(100vh - 4rem);">
                        
                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/80 backdrop-blur shrink-0">
                            <h3 class="text-lg font-bold text-slate-800" x-text="title"></h3>
                            <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none transition-colors rounded-full p-1 hover:bg-slate-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="px-6 py-6 overflow-y-auto flex-1 min-h-0 text-slate-600 text-sm leading-relaxed whitespace-pre-wrap" x-text="content"></div>
                        
                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/80 backdrop-blur flex justify-end shrink-0">
                            <button @click="closeModal()" class="px-5 py-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-700 font-medium transition-colors shadow-sm text-sm">Close</button>
                        </div>
                    </div>
                </div>
            </footer>

            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('footerModals', () => ({
                        isOpen: false,
                        title: '',
                        content: '',
                        policies: {
                            'Legal Disclaimer': 'All efforts have been made to make the information as accurate as possible. The respective Departments, Govt of West Bengal or Department of Finance as Nodal or NIC will not be responsible for any loss due to inaccuracy. Any discrepancy found may be brought to the notice of the concerned department.',
                            'Copyright Policy': 'The contents on this website may not be reproduced partially or fully, without duly & prominently acknowledging the source. The contents of this website cannot be used in any misleading or objectionable context or derogatory manner. However the permission to reproduce the material available on this website shall not extend to any material which is identified as being copyright of a third party. Authorization to reproduce such material must be obtained from the Departments/copyright holders concerned.',
                            'Privacy Policy': 'Though all efforts have been made to ensure the accuracy of the content on this application, the same should not be construed as a statement of law or used for any legal purposes. Respective Departments accepts no responsibility in relation to the accuracy, completeness, usefulness or otherwise, of the contents. Users are advised to verify/check any information, and to obtain any appropriate professional advice before acting on the information provided on this application. This portal does not automatically capture any specific personal information from any user (like name, phone no. or e-mail address) that allows this Directorate to identify any user individually when users visit the site. Users can generally visit the site without revealing Personal Information, unless users choose to provide such information.',
                            'Hyperlink Policy': 'At many places in this application, you will find links to other applications/websites/portals. These links have been placed for your convenience. Respective Departments is not in any way responsible for the contents and reliability of the linked websites and does not necessarily endorse the views expressed in by them. Mere presence of the link or its listing on this website should not be assumed as endorsement of any kind. We cannot guarantee that these links will work all the time and we have no control over availability of linked pages.',
                            'Terms & Condition': 'In case of any variance between what is stated and that contained in the relevant Acts, Rules, Regulations, Policy, Statements, etc, the latter shall prevail. Under no circumstances will Respective Departments be liable for any expense, loss or damage including, without limitation, indirect or consequential loss or damage, or any expense, loss or damage whatsoever arising from use, or loss of use, of data, arising out of or in connection with the use of this website. These terms and conditions shall be governed by and construed in accordance with the Indian Laws. Any dispute arising under these terms and conditions shall be subject to the jurisdiction of the courts of India. The information posted on this website could include hypertext links or pointers to information created and maintained by non-Government / private organizations. Respective Departments is providing these links and pointers solely for your information and convenience. When you select a link to an This website is designed, developed and maintained by National Informatics Centre (NIC) and content provided by Respective Departments for the information to general public. The documents and information displayed in this website are for reference purposes only and do not purport to a legal document. Though all efforts have been made to ensure the accuracy and currency of the content on this website, the same should not be construed as a statement of law or used for any legal purposes. In case of any ambiguity or doubts, users are advised to verify / check with the Department(s) and / or other source(s), and to obtain appropriate professional advice before use of information. You are leaving this website and are subject to the privacy and security policies of the owners / sponsors of the outside website. Respective Departments does not guarantee the availability of such linked pages at all times. Respective Departments cannot authorize the use of copyrighted materials contained in linked websites. Users are advised to request such authorization from the owner of the linked website. Respective Departments does not guarantee that linked websites comply with Indian Government Web Guidelines. Respective Departments neither endorses in any way nor offers any judgment or warranty and accepts no responsibility or liability for the authenticity, availability of any of the goods or services or for any damage, loss or harm, directly or consequential or any violation of international or local laws that may be incurred by your visiting and transacting on these websites.'
                        },
                        openModal(key) {
                            this.title = key;
                            this.content = this.policies[key];
                            this.isOpen = true;
                            document.body.style.overflow = 'hidden';
                        },
                        closeModal() {
                            this.isOpen = false;
                            document.body.style.overflow = '';
                        }
                    }))
                })
            </script>
