{{--
    app-modules/profile/resources/views/public-profile.blade.php

    Mock do front-end da página pública de perfil (issue #257).
    Dados estáticos para aprovação visual do layout.
    Sem migrations, models, enums ou lógica de backend.
--}}
<x-portal::layouts.app title="Rafael Mendes — He4rt Devs">
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        @vite(['app-modules/profile/resources/css/profile.css'])
    @endpush
<div class="mx-auto max-w-6xl px-4 py-10">
  <div class="grid grid-cols-1 gap-8 lg:grid-cols-[300px_1fr]">

    <!-- Sidebar -->
    <aside class="flex flex-col gap-5 lg:sticky lg:top-10 lg:self-start">

      <div class="flex justify-center lg:justify-start">
        <div class="avatar-wrap">
          <div class="avatar-ring-glow"></div>
          <div class="avatar-core">RM</div>
        </div>
      </div>

      <div class="text-center lg:text-left">
        <h1 class="text-2xl font-bold">Rafael Mendes</h1>
        <p class="text-[#6e6590] text-sm">@rafaelmendes</p>
        <div class="glow-rule mt-2 mx-auto lg:mx-0"></div>
      </div>

      <div class="flex items-center justify-center gap-2 lg:justify-start">
        <p class="text-[#a8a0c0] text-sm">Java Backend Developer</p>
        <span class="inline-flex items-center rounded-full bg-[#9b5de5]/10 px-2.5 py-0.5 text-[11px] font-medium text-[#9b5de5] border border-[#9b5de5]/20">
          Pleno
        </span>
      </div>

      <div class="flex items-center justify-center gap-1.5 text-sm text-[#a8a0c0] lg:justify-start">
        <i class="fa-solid fa-location-dot text-xs text-[#6e6590]"></i>
        Belo Horizonte, MG
      </div>

      <!-- availability filter chips -->
      <div class="flex flex-wrap items-center justify-center gap-1.5 lg:justify-start">
        <span class="inline-flex items-center gap-1 rounded-full border border-green-500/30 bg-green-500/10 px-2.5 py-1 text-[11px] font-medium text-green-300">
          <i class="fa-solid fa-check text-[10px]"></i> Início imediato
        </span>
        <span class="inline-flex items-center gap-1 rounded-full border border-green-500/30 bg-green-500/10 px-2.5 py-1 text-[11px] font-medium text-green-300">
          <i class="fa-solid fa-check text-[10px]"></i> Remoto
        </span>
        <span class="inline-flex items-center gap-1 rounded-full border border-[rgba(155,93,229,0.16)] bg-[#1a1728] px-2.5 py-1 text-[11px] font-medium text-[#6e6590]">
          CLT
        </span>
        <span class="inline-flex items-center gap-1 rounded-full border border-green-500/30 bg-green-500/10 px-2.5 py-1 text-[11px] font-medium text-green-300">
          <i class="fa-solid fa-check text-[10px]"></i> PJ
        </span>
        <span class="inline-flex items-center gap-1 rounded-full border border-green-500/30 bg-green-500/10 px-2.5 py-1 text-[11px] font-medium text-green-300">
          <i class="fa-solid fa-check text-[10px]"></i> Freelance
        </span>
      </div>

      <!-- social icons -->
      <div class="flex items-center justify-center gap-2 lg:justify-start">
        <a href="#" target="_blank" class="text-[#a8a0c0] hover:text-white hover:bg-[#9b5de5]/20 flex size-9 items-center justify-center rounded-lg bg-[#1a1728] border border-[rgba(155,93,229,0.16)] transition-colors">
          <i class="fa-brands fa-dev text-base"></i>
        </a>
        <a href="#" target="_blank" class="text-[#a8a0c0] hover:text-white hover:bg-[#9b5de5]/20 flex size-9 items-center justify-center rounded-lg bg-[#1a1728] border border-[rgba(155,93,229,0.16)] transition-colors">
          <i class="fa-brands fa-whatsapp text-base"></i>
        </a>
        <a href="#" target="_blank" class="text-[#a8a0c0] hover:text-white hover:bg-[#9b5de5]/20 flex size-9 items-center justify-center rounded-lg bg-[#1a1728] border border-[rgba(155,93,229,0.16)] transition-colors">
          <i class="fa-brands fa-linkedin-in text-base"></i>
        </a>
        <a href="#" target="_blank" class="text-[#a8a0c0] hover:text-white hover:bg-[#9b5de5]/20 flex size-9 items-center justify-center rounded-lg bg-[#1a1728] border border-[rgba(155,93,229,0.16)] transition-colors">
          <i class="fa-brands fa-github text-base"></i>
        </a>
      </div>

      <a href="#" class="inline-flex items-center justify-center gap-2 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] px-4 py-2.5 text-sm font-medium transition-colors hover:border-[#9b5de5]">
        <i class="fa-solid fa-download text-sm text-[#a8a0c0]"></i>
        Baixar currículo
      </a>

      <div class="card p-5">
        <div class="flex items-center gap-3">
          <div class="flex size-10 items-center justify-center rounded-full bg-[#9b5de5]/10">
            <svg class="size-5 text-[#9b5de5]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          </div>
          <div>
            <p class="text-[#6e6590] text-xs">Nível da Comunidade</p>
            <p class="text-lg font-bold">14</p>
          </div>
        </div>
        <div class="mt-4">
          <div class="h-2 w-full overflow-hidden rounded-full bg-[#1a1728]">
            <div class="h-full rounded-full bg-gradient-to-r from-[#782bf1] to-[#9b5de5]" style="width: 67%"></div>
          </div>
          <p class="text-[#a8a0c0] mt-2 text-xs">8.400 XP · 1.600 para o próximo nível</p>
        </div>
        <div class="mt-3 border-t border-[rgba(155,93,229,0.16)] pt-3">
          <p class="text-[#6e6590] text-xs">Membro há 1 ano e 4 meses</p>
        </div>
      </div>

    </aside>

    <!-- Main -->
    <main class="flex flex-col gap-6">

      <div class="card p-6">
        <h2 class="mb-3 text-lg font-semibold">Sobre</h2>
        <p class="text-[#a8a0c0] text-sm leading-relaxed">
          Desenvolvedor backend com foco em Java e Spring Boot, atuando também com
          PHP/Laravel em projetos open source. Gosta de arquitetura limpa, automação
          de testes e mentorias para devs em início de carreira.
        </p>
      </div>

      <div class="card p-6">
        <h2 class="mb-4 text-lg font-semibold">Stack & Skills</h2>
        <div class="flex flex-col gap-4">

          <!-- 1. Java + Spring Boot + PHP + Laravel -->
          <div class="flex flex-wrap gap-2">
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-purple-500/20 bg-purple-500/10 px-2.5 py-1 text-xs font-medium text-purple-300">
              <i class="fa-brands fa-java text-[11px]"></i> Java
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-purple-500/20 bg-purple-500/10 px-2.5 py-1 text-xs font-medium text-purple-300">
              <i class="fa-solid fa-leaf text-[11px]"></i> Spring Boot
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-purple-500/20 bg-purple-500/10 px-2.5 py-1 text-xs font-medium text-purple-300">
              <i class="fa-brands fa-php text-[11px]"></i> PHP
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-purple-500/20 bg-purple-500/10 px-2.5 py-1 text-xs font-medium text-purple-300">
              <i class="fa-brands fa-laravel text-[11px]"></i> Laravel
            </span>
          </div>

          <div class="skill-divider"></div>

          <!-- 2. Infraestrutura & BD -->
          <div class="flex flex-wrap gap-2">
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-cyan-500/20 bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-300">
              <i class="fa-solid fa-database text-[11px]"></i> PostgreSQL
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-cyan-500/20 bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-300">
              <i class="fa-solid fa-database text-[11px]"></i> MySQL
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-cyan-500/20 bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-300">
              <i class="fa-brands fa-docker text-[11px]"></i> Docker
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-cyan-500/20 bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-300">
              <i class="fa-brands fa-git-alt text-[11px]"></i> Git
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-cyan-500/20 bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-300">
              <i class="fa-solid fa-database text-[11px]"></i> Flyway
            </span>
          </div>

          <div class="skill-divider"></div>

          <!-- 3. Outros: soft skills + product thinking -->
          <div class="flex flex-wrap gap-2">
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] px-2.5 py-1 text-xs font-medium text-[#a8a0c0]">
              <i class="fa-solid fa-lightbulb text-[11px]"></i> Product Thinking
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] px-2.5 py-1 text-xs font-medium text-[#a8a0c0]">
              <i class="fa-solid fa-people-group text-[11px]"></i> Liderança de equipe
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] px-2.5 py-1 text-xs font-medium text-[#a8a0c0]">
              <i class="fa-solid fa-chalkboard-user text-[11px]"></i> Mentoria
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] px-2.5 py-1 text-xs font-medium text-[#a8a0c0]">
              <i class="fa-solid fa-comments text-[11px]"></i> Comunicação com stakeholders
            </span>
          </div>

          <div class="skill-divider"></div>

          <!-- 4. Idiomas -->
          <div class="flex flex-wrap gap-2">
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] px-2.5 py-1 text-xs font-medium text-[#a8a0c0]">
              Português · Nativo
            </span>
            <span class="skill-chip inline-flex items-center gap-1.5 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] px-2.5 py-1 text-xs font-medium text-[#a8a0c0]">
              Inglês · Intermediário
            </span>
          </div>

        </div>
      </div>

      <!-- Projetos -->
      <div class="card p-6">
        <h2 class="mb-4 text-lg font-semibold">Projetos</h2>

        <div class="rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] p-5 mb-3">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
              <h3 class="text-sm font-semibold">finance-tracker-api</h3>
              <p class="text-[#a8a0c0] mt-1 text-xs leading-relaxed">
                API REST para controle financeiro pessoal, com autenticação JWT,
                categorização automática de despesas e relatórios mensais.
              </p>
            </div>
            <a href="#" class="text-[#a8a0c0] hover:text-[#9b5de5] shrink-0 transition-colors">
              <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
            </a>
          </div>
          <div class="mt-3 flex flex-wrap gap-1.5">
            <span class="rounded-md bg-purple-500/10 px-1.5 py-0.5 text-[10px] font-medium text-purple-300">Java</span>
            <span class="rounded-md bg-purple-500/10 px-1.5 py-0.5 text-[10px] font-medium text-purple-300">Spring Boot</span>
            <span class="rounded-md bg-green-500/10 px-1.5 py-0.5 text-[10px] font-medium text-green-300">PostgreSQL</span>
            <span class="rounded-md bg-[#1a1728] border border-[rgba(155,93,229,0.16)] px-1.5 py-0.5 text-[10px] font-medium text-[#a8a0c0]">Docker</span>
          </div>
          <div class="mt-3 border-t border-[rgba(155,93,229,0.16)] pt-3 flex items-center gap-4 text-[11px] text-[#6e6590]">
            <span class="inline-flex items-center gap-1"><i class="fa-solid fa-star text-[10px]"></i> 34</span>
            <span class="inline-flex items-center gap-1"><i class="fa-solid fa-code-fork text-[10px]"></i> 9</span>
            <span>Atualizado há 3 dias</span>
          </div>
        </div>

        <div class="rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] p-5">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
              <h3 class="text-sm font-semibold">task-board-laravel</h3>
              <p class="text-[#a8a0c0] mt-1 text-xs leading-relaxed">
                Quadro Kanban colaborativo em Laravel + Livewire, com notificações
                em tempo real e permissões por papel de usuário.
              </p>
            </div>
            <a href="#" class="text-[#a8a0c0] hover:text-[#9b5de5] shrink-0 transition-colors">
              <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
            </a>
          </div>
          <div class="mt-3 flex flex-wrap gap-1.5">
            <span class="rounded-md bg-blue-500/10 px-1.5 py-0.5 text-[10px] font-medium text-blue-300">PHP</span>
            <span class="rounded-md bg-blue-500/10 px-1.5 py-0.5 text-[10px] font-medium text-blue-300">Laravel</span>
            <span class="rounded-md bg-green-500/10 px-1.5 py-0.5 text-[10px] font-medium text-green-300">MySQL</span>
          </div>
          <div class="mt-3 border-t border-[rgba(155,93,229,0.16)] pt-3 flex items-center gap-4 text-[11px] text-[#6e6590]">
            <span class="inline-flex items-center gap-1"><i class="fa-solid fa-star text-[10px]"></i> 18</span>
            <span class="inline-flex items-center gap-1"><i class="fa-solid fa-code-fork text-[10px]"></i> 4</span>
            <span>Atualizado há 2 semanas</span>
          </div>
        </div>
      </div>

      <!-- PRs -->
      <div class="card p-6">
        <h2 class="mb-4 text-lg font-semibold">PRs na Comunidade</h2>
        <div class="flex flex-col gap-3">
          <div class="rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium">feat: adiciona filtro por categoria na listagem</p>
                <p class="text-[#a8a0c0] mt-0.5 text-xs">heartdevs / heartdevs.com</p>
              </div>
              <span class="shrink-0 rounded-full bg-green-500/10 px-2 py-0.5 text-[10px] font-medium text-green-300">
                Merged
              </span>
            </div>
            <div class="mt-2 flex items-center gap-3 text-[11px] text-[#6e6590]">
              <span class="inline-flex items-center gap-1"><i class="fa-solid fa-code-merge text-[10px]"></i> #341</span>
              <span>5 dias atrás</span>
            </div>
          </div>
          <div class="rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium">fix: corrige cálculo de XP no nível 5</p>
                <p class="text-[#a8a0c0] mt-0.5 text-xs">heartdevs / heartdevs.com</p>
              </div>
              <span class="shrink-0 rounded-full bg-green-500/10 px-2 py-0.5 text-[10px] font-medium text-green-300">
                Merged
              </span>
            </div>
            <div class="mt-2 flex items-center gap-3 text-[11px] text-[#6e6590]">
              <span class="inline-flex items-center gap-1"><i class="fa-solid fa-code-merge text-[10px]"></i> #329</span>
              <span>3 semanas atrás</span>
            </div>
          </div>
          <div class="rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium">docs: atualiza guia de contribuição</p>
                <p class="text-[#a8a0c0] mt-0.5 text-xs">heartdevs / heartdevs.com</p>
              </div>
              <span class="shrink-0 rounded-full bg-yellow-500/10 px-2 py-0.5 text-[10px] font-medium text-yellow-300">
                Open
              </span>
            </div>
            <div class="mt-2 flex items-center gap-3 text-[11px] text-[#6e6590]">
              <span class="inline-flex items-center gap-1"><i class="fa-solid fa-code-pull-request text-[10px]"></i> #355</span>
              <span>hoje</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Badges -->
      <div class="card p-6">
        <h2 class="mb-3 text-lg font-semibold">Badges He4rt</h2>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div class="flex items-center gap-3 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] p-4">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#9b5de5]/10">
              <svg class="size-5 text-[#9b5de5]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
              <h4 class="text-sm font-medium">Contribuidor Open Source</h4>
              <p class="text-[#a8a0c0] mt-0.5 text-xs">10+ pull requests mergeadas</p>
            </div>
          </div>
          <div class="flex items-center gap-3 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] p-4">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#9b5de5]/10">
              <svg class="size-5 text-[#9b5de5]" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
              <h4 class="text-sm font-medium">Mentor da Comunidade</h4>
              <p class="text-[#a8a0c0] mt-0.5 text-xs">Ajudou 25+ desenvolvedores</p>
            </div>
          </div>
          <div class="flex items-center gap-3 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] p-4">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#9b5de5]/10">
              <svg class="size-5 text-[#9b5de5]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"/></svg>
            </div>
            <div class="min-w-0 flex-1">
              <h4 class="text-sm font-medium">Speaker</h4>
              <p class="text-[#a8a0c0] mt-0.5 text-xs">Palestrou em evento da comunidade</p>
            </div>
          </div>
          <div class="flex items-center gap-3 rounded-lg border border-[rgba(155,93,229,0.16)] bg-[#1a1728] p-4">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#9b5de5]/10">
              <svg class="size-5 text-[#9b5de5]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
            </div>
            <div class="min-w-0 flex-1">
              <h4 class="text-sm font-medium">Streak de 30 dias</h4>
              <p class="text-[#a8a0c0] mt-0.5 text-xs">Ativo na comunidade por 30 dias seguidos</p>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>
</x-portal::layouts.app>