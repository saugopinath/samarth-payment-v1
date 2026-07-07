<?php use function Laravel\Folio\{name}; name('terms'); ?>
<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public.guest')] class extends Component {}; ?>
@component('layouts.public.guest')
@volt

<div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 w-full">
    <div class="mb-4">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-amber-600 font-medium transition-colors" wire:navigate>
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Home
        </a>
    </div>
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
        <div class="p-6 md:p-8">
            <h1 class="text-3xl font-extrabold text-slate-800 mb-8 border-b border-slate-100 pb-4 text-center">Terms & Conditions</h1>
            <div class="text-slate-600 leading-relaxed text-base md:text-lg" style="text-align: justify;">
                <p>
                    In case of any variance between what is stated and that contained in the relevant Acts, Rules, Regulations, Policy, Statements, etc, the latter shall prevail. Under no circumstances will Respective Departments be liable for any expense, loss or damage including, without limitation, indirect or consequential loss or damage, or any expense, loss or damage whatsoever arising from use, or loss of use, of data, arising out of or in connection with the use of this website. These terms and conditions shall be governed by and construed in accordance with the Indian Laws. Any dispute arising under these terms and conditions shall be subject to the jurisdiction of the courts of India.
                </p>
                <p class="mt-6">
                    The information posted on this website could include hypertext links or pointers to information created and maintained by non-Government / private organizations. Respective Departments is providing these links and pointers solely for your information and convenience. When you select a link to an This website is designed, developed and maintained by National Informatics Centre (NIC) and content provided by Respective Departments for the information to general public. The documents and information displayed in this website are for reference purposes only and do not purport to a legal document.
                </p>
                <p class="mt-6">
                    Though all efforts have been made to ensure the accuracy and currency of the content on this website, the same should not be construed as a statement of law or used for any legal purposes. In case of any ambiguity or doubts, users are advised to verify / check with the Department(s) and / or other source(s), and to obtain appropriate professional advice before use of information. You are leaving this website and are subject to the privacy and security policies of the owners / sponsors of the outside website. Respective Departments does not guarantee the availability of such linked pages at all times.
                </p>
                <p class="mt-6">
                    Respective Departments cannot authorize the use of copyrighted materials contained in linked websites. Users are advised to request such authorization from the owner of the linked website. Respective Departments does not guarantee that linked websites comply with Indian Government Web Guidelines. Respective Departments neither endorses in any way nor offers any judgment or warranty and accepts no responsibility or liability for the authenticity, availability of any of the goods or services or for any damage, loss or harm, directly or consequential or any violation of international or local laws that may be incurred by your visiting and transacting on these websites.
                </p>
            </div>
        </div>
    </div>
</div>

@endvolt
@endcomponent
