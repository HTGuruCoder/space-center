<div>
    <style>
        .org-chart {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .org-node {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            margin: 1rem;
        }

        .org-node-card {
            background: oklch(var(--b1));
            border: 2px solid oklch(var(--bc) / 0.2);
            border-radius: 1rem;
            padding: 1rem;
            min-width: 200px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .org-node-card:hover {
            border-color: oklch(var(--p));
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            transform: translateY(-2px);
        }

        .org-node-card.current-user {
            border-color: oklch(var(--p));
            background: oklch(var(--p) / 0.1);
        }

        .org-children {
            display: flex;
            flex-direction: row;
            justify-content: center;
            gap: 2rem;
            margin-top: 3rem;
            position: relative;
        }

        /* Vertical line from parent to children container */
        .org-node.has-children::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            width: 2px;
            height: 2rem;
            background: oklch(var(--bc) / 0.2);
            transform: translateX(-50%);
        }

        /* Horizontal line connecting children */
        .org-children::before {
            content: '';
            position: absolute;
            top: -2rem;
            left: 0;
            right: 0;
            height: 2px;
            background: oklch(var(--bc) / 0.2);
        }

        /* Vertical lines from horizontal line to each child */
        .org-children > .org-node::before {
            content: '';
            position: absolute;
            top: -2rem;
            left: 50%;
            width: 2px;
            height: 2rem;
            background: oklch(var(--bc) / 0.2);
            transform: translateX(-50%);
        }

        /* Hide horizontal line if only one child */
        .org-children:has(> .org-node:only-child)::before {
            display: none;
        }

        /* Center the single vertical line if only one child */
        .org-children:has(> .org-node:only-child) > .org-node::before {
            left: 50%;
        }
    </style>

    <div class="mb-6">
        <h1 class="text-3xl font-bold">{{ __('Organization Chart') }}</h1>
        <p class="text-base-content/70">
            @if($hasSubordinates)
                {{ __('Your team structure') }}
            @else
                {{ __('You currently do not have any team members reporting to you') }}
            @endif
        </p>
    </div>

    {{-- Always show organization chart (even if just the current user) --}}
    <div class="overflow-x-auto pb-8">
        <div class="min-w-max flex justify-center">
            <div class="org-chart">
                @include('livewire.employee.subordinates.partials.org-node', ['node' => $orgTree, 'isRoot' => true])
            </div>
        </div>
    </div>
</div>
