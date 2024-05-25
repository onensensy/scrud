@props(['definitions', 'entry'])
{{-- @dd($entry) --}}
<div>
    <h4 class="card-title mb-5">
        <span class="badge bg-primary">{{ strtoupper($definitions->first()->workflow->action) }}</span>
        WORKFLOW
    </h4>
    <div class="">
        <ul class="verti-timeline list-unstyled">

            @foreach ($definitions as $definition)
                @php
                    $is_completed = false;
                    $is_current = false;
                    $is_pending = false;
                    $is_end = false;

                    $approvalEntriesCount = $definition->workflowmanagerApprovalEntries($entry)->count();
                    $requiredApprovals = $definition->workflowDefinitionApproval->approval_count;
                    if ($definition->final_step == 'Y' && $definition->id == $entry->workflow_definition_id) {
                        $icon = 'check-circle';
                        $text = 'Workflow Completed';
                        $is_completed = true;
                        $is_end = true;
                    } else {
                        if ($approvalEntriesCount >= $requiredApprovals) {
                            if ($definition->id == $entry->workflow_definition_id) {
                                $icon = 'check-circle text-danger';
                                $text = 'Completed';
                                $is_current = true;
                            } else {
                                $icon = 'check-circle';
                                $text = 'Completed';
                                $is_completed = true;
                            }
                        } elseif ($approvalEntriesCount > 0 && $approvalEntriesCount < $requiredApprovals) {
                            $icon = 'right-arrow-circle';
                            $is_current = true;
                            $text = 'In Progress...';
                        } elseif ($approvalEntriesCount == 0) {
                            $icon = 'history';
                            $is_pending = true;
                            $text = 'Pending';
                        }
                    }

                @endphp

                <li class="event-list {{ $is_current ? 'active' : '' }}">
                    <div class="event-timeline-dot">
                        <i
                            class="bx bx-{{ $icon }} {{ $is_completed ? 'text-primary ' : '' }} {{ $is_current ? 'bx-fade-right' : '' }} {{ $is_pending ? 'text-grey' : '' }} h2"></i>
                    </div>
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <div>
                                <h5> {{ $definition->role->name }}
                                    @if (!$loop->last)
                                        ({{ $approvalEntriesCount }}/{{ $requiredApprovals }})
                                    @endif
                                </h5>
                                <p class="text-muted">
                                    {{ $text }}
                                </p>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach

    </div>
</div>
